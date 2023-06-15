<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\ResponseKey;

use Drupal\dipas\Annotation\ResponseKey;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RelatedContributions.
 *
 * @ResponseKey(
 *   id = "relatedcontributions",
 *   description = @Translation("Returns related contributions for a given contribution id."),
 *   requestMethods = {
 *     "GET",
 *   },
 *   isCacheable = true
 * )
 *
 * @package Drupal\dipas\Plugin\ResponseKey
 */
class RelatedContributions extends ResponseKeyBase {

  use NodeListingTrait;
  use DateTimeTrait;
  use ContributionDetailsTrait;

  /**
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * {@inheritdoc}
   */
  protected function setAdditionalDependencies(ContainerInterface $container) {
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
      'related' => $this->getNodes(),
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
  protected function getLimit() {
    return 5;
  }

  /**
   * {@inheritdoc}
   */
  protected function getSortingField() {
    return 'created';
  }

  /**
   * {@inheritdoc}
   */
  protected function getSortingDirection() {
    return 'DESC';
  }

  /**
   * {@inheritdoc}
   */
  protected function getDateFormatter() {
    return $this->dateFormatter;
  }

}
