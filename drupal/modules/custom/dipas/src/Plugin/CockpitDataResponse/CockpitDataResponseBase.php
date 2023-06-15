<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\CockpitDataResponse;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\dipas\PluginSystem\CockpitDataResponsePluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\masterportal\DomainAwareTrait;
use Drupal\dipas\ProceedingListingMethodsTrait;

/**
 * Class CockpitDataResponseBase.
 *
 * @package Drupal\dipas\Plugin\CockpitDataResponse
 */
abstract class CockpitDataResponseBase implements CockpitDataResponsePluginInterface {

  use StringTranslationTrait;
  use DomainAwareTrait;
  use ProceedingListingMethodsTrait;

  /**
   * This plugins' defintion.
   *
   * @var array
   */
  protected $pluginDefinition;

  /**
   * Custom logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Custom configuration service.
   *
   * @var \Drupal\dipas\Controller\DipasConfigFactory
   */
  protected $dipasConfig;

  /**
   * The currently processed request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * The response object in construction.
   *
   * @var \Symfony\Component\HttpFoundation\JsonResponse
   */
  protected $response;

  /**
   * Drupal's entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Symfonys serializer service.
   *
   * @var \Symfony\Component\Serializer\Serializer
   */
  protected $serializer;

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * @var \Drupal\domain\DomainInterface|null
   */
  protected $activeDomain;

  /**
   * CockpitDataResponseBase constructor.
   *
   * @param array $pluginDefinition
   *   The plugin definition.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The currently processed request.
   * @param \Symfony\Component\HttpFoundation\JsonResponse $response
   *   The response object in construction.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   */
  public function __construct(array $pluginDefinition, Request $request, JsonResponse &$response) {
    $this->pluginDefinition = $pluginDefinition;
    $this->currentRequest = $request;
    $this->response = $response;
    // No nice dependency injection here.
    /* @var \Drupal\Core\DependencyInjection\Container $container */
    $container = \Drupal::getContainer();
    $this->state = $container->get('state');
    $this->configFactory = $container->get('config.factory');
    $this->logger = $container->get('logger.channel.dipas');
    $this->dipasConfig = $container->get('dipas.config');
    $this->entityTypeManager = $container->get('entity_type.manager');
    $this->serializer = $container->get('serializer');
    $this->database = $container->get('database');
    $this->languageManager = $container->get('language_manager');
    $this->moduleHandler = $container->get('module_handler');
    $this->setAdditionalDependencies($container);
  }

  /**
   * Allows plugin implementations to set their own dependencies.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   */
  protected function setAdditionalDependencies(ContainerInterface $container) {}

  /**
   * {@inheritdoc}
   */
  protected function getDatabase() {
    return $this->database;
  }

  /**
   * {@inheritdoc}
   */
  protected function getDipasConfig() {
    return $this->dipasConfig;
  }

  /**
   * {@inheritdoc}
   */
  public function getCookies() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  final public function getCacheTags() {
    $commonCacheTags = ['dipasCockpitDataEndpoint', "dipasCockpitDataEndpoint:{$this->pluginDefinition['id']}"];
    $pluginCacheTags = $this->getResponseKeyCacheTags();
    return array_unique(array_merge($commonCacheTags, $pluginCacheTags));
  }

  /**
   * Returns the settings with maintenance message only.
   *
   * @return array
   *   Array with maintenanceMessage setting only.
   */
  protected function getMaintenanceMessage() {
    return [
      'maintenanceMode' => TRUE,
      'maintenanceMessage' => str_replace('@site', $this->dipasConfig->get('ProjectInformation.site_name'), $this->configFactory->get('system.maintenance')->get('message')),
    ];
  }

  /**
   * Helper function to retrieve the proceeding configuration object.
   *
   * @param string $domainid
   *   The proceeding id.
   *
   * @return \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   *   The desired configuration object.
   */
  protected function getConfig($domainid) {
    $configs = drupal_static('dipas_domain_configs', []);

    if (!isset($configs[$domainid])) {
      $configs[$domainid] = $this->configFactory->get(sprintf('dipas.%s.configuration', $domainid));
    }

    return $configs[$domainid];
  }

  /**
   * {@inheritdoc}
   */
  final public function getResponseData() {
    if ($this->state->get('system.maintenance_mode') === 1) {
      return $this->getMaintenanceMessage();
    }
    else {
      return $this->getPluginResponse();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function postProcessResponse(array $responsedata) {
    return $responsedata;
  }

  /**
   * Return the key-specific cache tags.
   *
   * @return array
   */
  abstract protected function getResponseKeyCacheTags();

  /**
   * Returns the plugin response data array.
   *
   * @return array
   *   The array containing the plugin response data.
   */
  abstract protected function getPluginResponse();

}
