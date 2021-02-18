<?php

namespace Drupal\dipas\Plugin\views\field;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\State\StateInterface;
use Drupal\masterportal\DomainAwareTrait;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

abstract class DipasScoreFieldBase extends FieldPluginBase implements ContainerFactoryPluginInterface {

  use DomainAwareTrait;

  /**
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('state'),
      $container->get('request_stack')
    );
  }

  /**
   * DipasScoreFieldBase constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\Core\State\StateInterface $state
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    StateInterface $state,
    RequestStack $request_stack
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->state = $state;
    $this->currentRequest = $request_stack->getCurrentRequest();
    unset($this->field_alias);
  }

  /**
   * {@inheritdoc}
   */
  public function query() {}

  /**
   * Returns the score values (singleton).
   *
   * @return array
   */
  protected function getScoreValues() {
    $scoreResults = drupal_static('dipas_nlp_score_values', FALSE);
    if ($scoreResults === FALSE) {
      $statekey = 'dipas.nlp.score.result';
      if ($this->isDomainModuleInstalled()) {
        $statekey .= sprintf(':%s', $this->getActiveDomain());
      }
      if (($scoreData = $this->state->get($statekey)) !== NULL) {
        $scoreResults = $scoreData['result'];
      }
      else {
        $scoreResults = [];
      }
    }
    return $scoreResults;
  }

  /**
   * Returns the score values for a given node id.
   *
   * @param int $nid
   *
   * @return array|FALSE
   */
  protected function getScoreValuesForNid($nid) {
    $scores = array_filter(
      $this->getScoreValues(),
      function ($item) use ($nid) {
        return (int) $item->id === (int) $nid;
      }
    );
    return !empty($scores)
      ? (array) reset($scores)->scores
      : FALSE;
  }

  /**
   * Returns a specific score value for a given node id.
   *
   * @param int $nid
   * @param string $type
   *
   * @return string|void
   */
  protected function getSpecificScoreValueforNid($nid, $type) {
    if (($scores = $this->getScoreValuesForNid($nid)) && isset($scores[$type])) {
      return $scores[$type];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    return $this->getSpecificScoreValueforNid((int) $values->nid, $this->getScorePropertyName());
  }

  /**
   * {@inheritdoc}
   */
  public function postExecute(&$values) {
    if (
      $this->currentRequest->query->has('order') &&
      $this->currentRequest->query->get('order') === $this->pluginId
    ) {
      $order = $this->currentRequest->query->has('sort')
        ? strtoupper($this->currentRequest->query->get('sort'))
        : 'ASC';

      $scoreValues = [];
      foreach ($values as $index => $value) {
        $scoreValues[$index] = $this->getSpecificScoreValueforNid($value->nid, $this->getScorePropertyName());
      }
      array_multisort($scoreValues, SORT_NUMERIC, $values);
      if ($order === 'DESC') {
        $values = array_reverse($values);
      }
    }
  }

  /**
   * Returns the name of the desired score property.
   *
   * @return string
   */
  abstract protected function getScorePropertyName();

}
