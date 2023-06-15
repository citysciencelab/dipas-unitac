<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\ResponseKey;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\image\Entity\ImageStyle;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait NodeContentTrait {

  /**
   * {@inheritdoc}
   *
   * @throws \Exception
   */
  public function getPluginResponse() {
    if ($node = $this->loadNode()) {

      $content = [
        'nid' => $node->id(),
        'bundle' => $node->bundle(),
        'langcode' => $node->language()->getId(),
        'title' => $node->label(),
        'created' => $this->convertTimestampToUTCDateTimeString($node->getCreatedTime(), FALSE),
        'author' => $node->getRevisionUser()->getDisplayName(),
        'content' => [],
      ];

      foreach ($node->get('field_content')->getValue() as $fieldDelta) {
        /* @var \Drupal\Core\Entity\ContentEntityInterface $paragraph */
        if ($paragraph = $this->getEntityTypeManager()->getStorage('paragraph')->load($fieldDelta['target_id'])) {
          $content['content'][] = $this->parseEntityContent($paragraph);
        }
      }

      return $content;
    }
    else {
      throw new NotFoundHttpException();
    }
  }

  /**
   * Parse the contents of a given entity.
   *
   * @param ContentEntityInterface $entity
   *   The entity to parse.
   * @param string $viewmode
   *   The view mode the entity should get rendered in.
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function parseEntityContent(ContentEntityInterface $entity, $viewmode = 'default') {
    $result = [
      'type' => $entity->getEntityTypeId(),
      'bundle' => $entity->bundle(),
    ];

    if ($entity->getEntityTypeId() === 'node') {
      $result['nid'] = $entity->id();
      $result['title'] = $entity->label();
    }

    $bundleFields = \Drupal::service('dipas.entity_services')->getEntityTypeBundleFieldsInViewMode(
      $entity->getEntityTypeId(),
      $entity->bundle(),
      $viewmode,
      TRUE,
      ['field_comments', 'field_rating']
    );

    foreach ($bundleFields as $field => $definition) {
      foreach ($entity->get($field)->getValue() as $delta => $value) {
        switch ($definition->getType()) {
          case 'entity_reference_revisions':
          case 'entity_reference':
            $entityTypeId = $definition->getSetting('target_type');
            $entityId = $value['target_id'];
            $subentity = $this->getEntityTypeManager()->getStorage($entityTypeId)->load($entityId);
            $result[$field][$delta] = $this->parseEntityContent($subentity, $definition->display['settings']['view_mode']);
            break;

          case 'string':
          case 'text_long':
            $result[$field][$delta] = trim($entity->get($field)->getValue()[$delta]['value']);
            break;

          case 'video_embed_field':
            $formatter = \Drupal::service('plugin.manager.field.formatter')->createInstance(
              'video_embed_field_video',
              [
                'field_definition' => $definition,
                'settings' => $definition->display['settings'],
                'label' => $definition->display['label'],
                'view_mode' => 'default',
                'third_party_settings' => $definition->display['third_party_settings'],
              ]
            );
            $renderarray = $formatter->view($entity->get($field));
            $html = \Drupal::service('renderer')->render($renderarray);
            $iframe = simplexml_load_string($html)->xpath('//iframe')[0]->asXML();
            $iframe = preg_replace('~src=("|\')https?://~', 'src=\1//', $iframe);
            $result[$field][$delta] = $iframe;
            break;

          case 'image':
            $file = $this->getEntityTypeManager()->getStorage('file')->load($value['target_id']);
            switch ($definition->display['type']) {
              case 'responsive_image':
                $image = \Drupal::service('image.factory')->get($file->getFileUri());
                $renderarray = [
                  '#theme' => 'responsive_image',
                  '#width' => $image->isValid() ? $image->getWidth() : NULL,
                  '#height' => $image->isValid() ? $image->getHeight() : NULL,
                  '#responsive_image_style_id' => $definition->display['settings']['responsive_image_style'],
                  '#uri' => $file->getFileUri(),
                ];
                $html = trim(\Drupal::service('renderer')->render($renderarray));
                $result[$field][$delta]['html'] = $html;
                $result[$field][$delta]['origin'] = $this->getFileUrlGenerator()->generateAbsoluteString($file->getFileUri());
                break;

              case 'image':
              default:
                if (!empty($definition->display['settings']['image_style'])) {
                  $result[$field][$delta]['url'] = $this->stripProtocolIndicatorFromUrl(
                    ImageStyle::load($definition->display['settings']['image_style'])->buildUrl($file->getFileUri())
                  );
                }
                else {
                  $result[$field][$delta]['url'] = $this->stripProtocolIndicatorFromUrl(
                    $this->getFileUrlGenerator()->generateAbsoluteString($file->getFileUri())
                  );
                }
                $result[$field][$delta]['origin'] = $this->stripProtocolIndicatorFromUrl(
                  $this->getFileUrlGenerator()->generateAbsoluteString($file->getFileUri())
                );
                $imageStyles = $this->getContentImageStyleList();
                $result[$field][$delta]['srcset'] = [];
                foreach ($imageStyles as $style) {
                  $result[$field][$delta]['srcset'][$style['width']] = $this->stripProtocolIndicatorFromUrl(
                    ImageStyle::load($style['style'])->buildUrl($file->getFileUri())
                  );
                }
                break;
            }
            if (!empty($value['alt'])) {
              $result[$field][$delta]['alt'] = $value['alt'];
            }
            break;

          default:
            $result[$field][$delta] = $value;
            break;
        }
      }

      // Flatten the field result array if only one entry exists.
      // Exception:
      // field "field_content" of paragraph types "division_in_planning_subareas"
      // and "planning_subarea" should never get flattened.
      if (
        isset($result[$field]) &&
        is_array($result[$field]) &&
        count($result[$field]) === 1 &&
        (
          !in_array(
            sprintf('%s.%s', $result['type'], $result['bundle']),
            [
              'paragraph.division_in_planning_subareas',
              'paragraph.planning_subarea',
            ]
          )
          ||
          $field !== 'field_content'
        )
      ) {
        $result[$field] = array_shift($result[$field]);
      }

    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  protected function getResponseKeyCacheTags() {
    return [sprintf('node:%d', $this->loadNode()->id())];
  }

  /**
   * Loads a node object (singleton).
   *
   * @return \Drupal\node\NodeInterface
   */
  protected function loadNode() {
    static $node = NULL;
    if (is_null($node) && !empty($nid = $this->getNodeId()) && is_numeric($nid)) {
      /* @var \Drupal\node\NodeInterface $node */
      $node = $this->getEntityTypeManager()->getStorage('node')->load($nid);
    }
    return $node;
  }

  /**
   * Returns the ID of the node that should get loaded.
   *
   * @return int
   *   The node id.
   */
  protected function getNodeId() {
    return $this->dipasConfig->get($this->getContentConfigPath());
  }

  /**
   * Returns the path to the configured node id to load.
   *
   * @return string
   */
  protected function getContentConfigPath() {
    return '';
  }

  /**
   * Strips out the leading "http(s)" from an url.
   *
   * @param string $url
   *  The input URL.
   *
   * @return string
   *   The URL without the protocol indicator.
   */
  protected function stripProtocolIndicatorFromUrl($url) {
    return preg_replace('~^https?://~', '//', $url);
  }

  /**
   * Returns a list of content image styles with their configured width.
   *
   * @return array
   *   An array of content images styles, keyed by their image style name
   */
  abstract protected function getContentImageStyleList();

  /**
   * Formats a given DateTime object into an UTC datetime string.
   *
   * @param int $timestamp
   * @param boolean $isUTC
   *
   * @return string
   * @throws \Exception
   */
  abstract protected function convertTimestampToUTCDateTimeString($timestamp, $isUTC);

  /**
   * Returns the node storage interface.
   *
   * @return \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  abstract protected function getEntityTypeManager();

  /**
   * @return \Drupal\Core\File\FileUrlGeneratorInterface
   */
  abstract function getFileUrlGenerator();

}
