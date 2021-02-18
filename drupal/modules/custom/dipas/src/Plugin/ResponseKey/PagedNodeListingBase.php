<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\ResponseKey;

use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class PagedNodeListingBase extends ResponseKeyBase implements PagedNodeListingInterface {

  use PagedNodeListingTrait;
  use DateTimeTrait;

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
  protected function getDateFormatter() {
    return $this->dateFormatter;
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
    if (
      $this->currentRequest->query->has('direction') &&
      ($direction = $this->currentRequest->query->get('direction')) &&
      in_array(strtoupper($direction), ['ASC', 'DESC'])
    ) {
      return strtoupper($direction);
    }
    return 'DESC';
  }

  /**
   * {@inheritdoc}
   */
  protected function getJoins() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  protected function getConditions() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  protected function getGroupBy() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getExpressions() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  protected function postProcessNodeData(array &$nodes) {}

}
