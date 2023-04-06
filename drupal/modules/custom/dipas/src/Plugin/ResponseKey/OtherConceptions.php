<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\ResponseKey;

use Drupal\dipas\Annotation\ResponseKey;
use Drupal\image\Entity\ImageStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class OtherConceptions.
 *
 * @ResponseKey(
 *   id = "otherconceptions",
 *   description = @Translation("Returns other conceptions for a given conception id."),
 *   requestMethods = {
 *     "GET",
 *   },
 *   isCacheable = true
 * )
 *
 * @package Drupal\dipas\Plugin\ResponseKey
 */
class OtherConceptions extends ResponseKeyBase {

  use NodeListingTrait;
  use DateTimeTrait;
  use ContributionDetailsTrait {
    ContributionDetailsTrait::getJoins as protected traitJoins;
    ContributionDetailsTrait::getGroupBy as protected traitGroupBy;
  }

  /**
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * {@inheritdoc}
   */
  public function setAdditionalDependencies(ContainerInterface $container) {
    $this->dateFormatter = $container->get('date.formatter');
  }

  /**
   * {@inheritdoc}
   */
  protected function getResponseKeyCacheTags() {
    return array_map(function ($node) {
      return sprintf('node:%d', $node->nid);
    }, $this->getNodes());
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginResponse() {
    return [
      'otherConceptions' => $this->getNodes(),
    ];
  }

  /**
   * Return related nodes.
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getNodes() {
    static $nodes = NULL;
    if ($nodes === NULL) {
      $nodes = $this->getQuery()->execute()->fetchAll();
      array_walk($nodes, function (&$node) {
        $node->created = $this->convertTimestampToUTCDateTimeString($node->created, FALSE);
        $node->field_conception_image = ImageStyle::load('conception_md')->buildUrl($node->field_conception_image);
      });
    }
    return $nodes;
  }

  /**
   * {@inheritdoc}
   */
  protected function getConditions() {
    return [
      [
        'field' => 'base.nid',
        'value' => $this->currentRequest->attributes->get('id'),
        'operator' => '<>',
      ]
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getSortingField() {
    return 'title';
  }

  /**
   * {@inheritdoc}
   */
  protected function getSortingDirection() {
    return 'ASC';
  }

  /**
   * {@inheritdoc}
   */
  protected function getNodeType() {
    return 'conception';
  }

  /**
   * {@inheritdoc}
   */
  protected function getJoins() {
    $joins = $this->traitJoins();

    // Remove category and rubric (not present on conceptions).
    for ($i = count($joins)-1; $i--; $i >= 0) {
      if (in_array($joins[$i]['alias'], ['category', 'rubric'])) {
        unset($joins[$i]);
      }
    }

    // Add conception image field.
    $joins[] = [
      'type' => 'LEFT',
      'table' => 'node__field_conception_image',
      'alias' => 'cimage',
      'condition' => 'base.type = cimage.bundle AND base.nid = cimage.entity_id AND base.vid = cimage.revision_id AND attr.langcode = cimage.langcode AND cimage.deleted = 0',
    ];
    $joins[] = [
      'type' => 'INNER',
      'table' => 'file_managed',
      'alias' => 'cfile',
      'condition' => 'cimage.field_conception_image_target_id = cfile.fid AND cfile.status = 1 AND cfile.langcode = attr.langcode',
      'fields' => [
        'uri' => 'field_conception_image',
      ],
    ];

    return $joins;
  }

  /**
   * {@inheritdoc}
   */
  protected function getGroupBy() {
    $groups = $this->traitGroupBy();

    // Remove category and rubric (not present on conceptions).
    for ($i = count($groups)-1; $i--; $i >= 0) {
      if (preg_match('~^(?:category|rubric)\.~', $groups[$i])) {
        unset($groups[$i]);
      }
    }

    // Add conception image field.
    $groups[] = 'cfile.uri';

    return $groups;
  }

  /**
   * {@inheritdoc}
   */
  protected function getDateFormatter() {
    return $this->dateFormatter;
  }

}
