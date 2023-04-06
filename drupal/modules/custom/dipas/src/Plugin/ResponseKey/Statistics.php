<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\ResponseKey;

use Drupal\dipas\Annotation\ResponseKey;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Statistics.
 *
 * @ResponseKey(
 *   id = "statistics",
 *   description = @Translation("Returns the data for the statistics page."),
 *   requestMethods = {
 *     "GET",
 *   },
 *   isCacheable = true
 * )
 *
 * @package Drupal\dipas\Plugin\ResponseKey
 */
class Statistics extends ResponseKeyBase {

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
  public function getPluginResponse() {
    return [
      'contributionData' => $this->getNodes(),
      'comments' => $this->getCommentCount(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getResponseKeyCacheTags() {
    $nodes = $this->getNodes();
    return array_map(function ($node) { return sprintf('node:%d', $node->nid); }, $nodes);
  }

  /**
   * Returns the total number of comments.
   *
   * @return int
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getCommentCount() {
    $nodes = $this->getNodes();
    $comments = 0;
    foreach ($nodes as $node) {
      $comments += $node->comments;
    }
    return $comments;
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
    return 'nid';
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
