<?php

namespace Drupal\dipas\Plugin\CockpitDataResponse;

use Drupal\dipas\Annotation\CockpitDataResponse;
use Drupal\image\Entity\ImageStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\dipas\Plugin\ResponseKey\NodeContentTrait;
use Drupal\dipas\Plugin\ResponseKey\DateTimeTrait;

/**
 * Class Page.
 *
 * @CockpitDataResponse(
 *   id = "page",
 *   description = @Translation("Returns the contents of a drupal node."),
 *   requestMethods = {
 *     "GET",
 *   },
 *   isCacheable = true,
 *   maxAge = 9999
 * )
 *
 * @package Drupal\dipas\Plugin\CockpitDataResponse
 */
class Page extends CockpitDataResponseBase {

  use DateTimeTrait;
  use NodeContentTrait;

  /**
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * {@inheritdoc}
   */
  protected function setAdditionalDependencies(ContainerInterface $container) {
    $this->state = $container->get('state');
    $this->dateFormatter = $container->get('date.formatter');
    $this->fileUrlGenerator = $container->get('file_url_generator');
  }

  /**
   * {@inheritdoc}
   */
  protected function getResponseKeyCacheTags() {
    return ['CockpitDataResponse', 'CockpitDataResponsePage'] +
      [sprintf('node-%d', $this->getNode()->id())];
  }

  /**
   * {@inheritdoc}
   */
  protected function getPluginResponse() {
    $node = $this->getNode();

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
      if ($paragraph = $this->entityTypeManager->getStorage('paragraph')->load($fieldDelta['target_id'])) {
        $content['content'][] = $this->parseEntityContent($paragraph);
      }
    }

    return $content;
  }

  /**
   * Returns the entity of a configured node (singleton).
   *
   * @return array|\Drupal\Core\Entity\EntityInterface|mixed
   *   The configured entity
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getNode() {
    $node = drupal_static('dipas.navigator.page');

    if (!$node) {
      $nid = $this->state->get('dipas.navigator.menu.' . $this->currentRequest->attributes->get('parameter'));

      if (!$nid || !($node = $this->entityTypeManager->getStorage('node')->load($nid))) {
        throw new NotFoundHttpException();
      }
    }

    return $node;
  }

  /**
   * {@inheritdoc}
   */
  protected function getDateFormatter() {
    return $this->dateFormatter;
  }

  /**
   * {@inheritdoc}
   */
  protected function getContentImageStyleList() {
    $styles = array_filter(
      ImageStyle::loadMultiple(),
      function ($imagestyle_name) {
        return preg_match('~^content_image_~', $imagestyle_name);
      },
      ARRAY_FILTER_USE_KEY
    );

    array_walk(
      $styles,
      function (&$style) {
        $config = $style->getEffects()->getConfiguration();
        $style = array_shift($config)['data']['width'];
      }
    );

    asort($styles);
    $styles = array_reverse($styles, TRUE);

    array_walk(
      $styles,
      function (&$item, $index) {
        $item = [
          'style' => $index,
          'width' => $item,
        ];
      }
    );

    return array_values($styles);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityTypeManager() {
    return $this->entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  protected function getFileUrlGenerator() {
    return $this->fileUrlGenerator;
  }

}
