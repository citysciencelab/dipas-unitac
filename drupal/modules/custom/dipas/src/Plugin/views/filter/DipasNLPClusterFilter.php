<?php

namespace Drupal\dipas\Plugin\views\filter;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\dipas\Service\DipasConfigInterface;
use Drupal\dipas\Service\DipasNlpServicesInterface;
use Drupal\views\Annotation\ViewsFilter;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\filter\ManyToOne;
use Drupal\views\ViewExecutable;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Field handler to filter contributions by their respective cluster.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsFilter("dipas_nlp_cluster_filter")
 */
class DipasNLPClusterFilter extends ManyToOne implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * @var \Drupal\dipas\Service\DipasNlpServicesInterface
   */
  protected $nlpServices;

  /**
   * @var \Drupal\dipas\Controller\DipasConfig
   */
  protected $config;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('dipas.nlp_services'),
      $container->get('dipas.config')
    );
  }

  /**
   * DipasNLPClusterFilter constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\dipas\Service\DipasNlpServicesInterface $nlp_services
   * @param \Drupal\dipas\Service\DipasConfigInterface $config
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    DipasNlpServicesInterface $nlp_services,
    DipasConfigInterface $config
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->nlpServices = $nlp_services;
    $this->config = $config;
  }

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    if ($this->config->get('NLPSettings.enabled') && $this->config->get('NLPSettings.enable_clustering')) {
      # correct?
      $this->valueTitle = t('Filter by cluster');
      $this->definition['options callback'] = [$this->nlpServices, 'getClusterOptions'];
    }
  }

  /**
   * Helper function that builds the query.
   */
  public function query() {
    if (!empty($this->value)) {
      $this->query->addWhere('AND', 'node_field_data.nid', $this->nlpServices->getIDsForCluster(reset($this->value)), 'IN');
    }
  }

}
