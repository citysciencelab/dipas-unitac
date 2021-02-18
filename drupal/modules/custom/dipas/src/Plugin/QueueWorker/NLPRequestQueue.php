<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\QueueWorker;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\PostponeItemException;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\Queue\SuspendQueueException;
use Drupal\Core\State\StateInterface;
use Drupal\dipas\Controller\DipasConfig;
use Drupal\dipas\Service\DipasNlpServicesInterface;
use Drupal\masterportal\DomainAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Processes Tasks for NLP Service Requests.
 *
 * @QueueWorker(
 *   id = "nlp_requests",
 *   title = @Translation("NLP request task worker: nlp request queue"),
 *   cron = {"time" = 60}
 * )
 */
class NLPRequestQueue extends QueueWorkerBase implements ContainerFactoryPluginInterface {
  use DomainAwareTrait;

  /**
   * @var \Drupal\dipas\Service\DipasNlpServicesInterface
   */
  protected $nlpService;

  /**
   * Drupal's entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Drupal's key-value-storage.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Custom configuration service.
   *
   * @var \Drupal\dipas\Controller\DipasConfig
   */
  protected $dipasConfig;

  /**
   * Contains a string with a subdomain suffix for state variables.
   *
   * @var string
   */
  protected $domainSuffix;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('entity_type.manager'),
      $container->get('state'),
      $container->get('dipasconfig.api'),
      $container->get('dipas.nlp_services')
    );
  }

  /**
   * Constructs a new NLPRequestQueue object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Drupal's entity type manager service.
   * @param \Drupal\Core\State\StateInterface $state
   *   Drupal's key-value storage service.
   * @param \Drupal\dipas\Controller\DipasConfig $dipas_config
   *   Custom configuration API service.
   * @param \Drupal\dipas\Service\DipasNlpServicesInterface $nlp_services
   *   Custom NLP service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    StateInterface $state,
    DipasConfig $dipas_config,
    DipasNlpServicesInterface $nlp_services
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entity_type_manager;
    $this->state = $state;
    $this->dipasConfig = $dipas_config;
    $this->nlpService = $nlp_services;

    $this->domainSuffix = sprintf(':%s', $this->getActiveDomain());
    $this->nodeStorage = $this->entityTypeManager->getStorage('node');
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $query = $this->nodeStorage->getQuery();
    $query->condition('type', 'contribution', '=');
    $query->condition('status', '1', '=');
    $this->makeEntityQueryDomainSensitive($query);
    $contribution_ids = $query->execute();
    $contributions = $this->nodeStorage->loadMultiple($contribution_ids);

    if (
      $this->dipasConfig->get('NLPSettings/enable_score_service') === TRUE &&
      !$this->nlpService->executeNlpScoresProcessing($contributions)
    ) {
      throw new SuspendQueueException('This queue run is skipped because of an unexpected error in requesting NLP scores service.');
    }

    if (
      $this->dipasConfig->get('NLPSettings/enable_clustering') === TRUE &&
      !$this->nlpService->executeNlpClusteringProcessing($contributions)
    ) {
      throw new SuspendQueueException('This queue run is skipped because of an unexpected error in requesting NLP clustering service.');
    }

    if (
      $this->dipasConfig->get('NLPSettings/enable_wordcloud') === TRUE &&
      !$this->nlpService->executeNlpWordcloudProcessing($contributions)
    ) {
      throw new SuspendQueueException('This queue run is skipped because of an unexpected error in requesting NLP wordcloud service.');
    }
  }

}
