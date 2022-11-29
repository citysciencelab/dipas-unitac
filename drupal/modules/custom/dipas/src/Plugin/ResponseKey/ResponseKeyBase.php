<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\ResponseKey;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\dipas\PluginSystem\ResponseKeyPluginInterface;
use Drupal\image\Entity\ImageStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ResponseKeyBase.
 *
 * @package Drupal\dipas\Plugin\ResponseKey
 */
abstract class ResponseKeyBase implements ResponseKeyPluginInterface {

  use StringTranslationTrait;

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
   * @var \Drupal\dipas\Controller\DipasConfig
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
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * @var bool
   */
  protected $domainModulePresent;

  /**
   * @var bool
   */
  protected $isSubdomain;

  /**
   * @var \Drupal\domain\DomainInterface|null
   */
  protected $activeDomain;

  /**
   * @var string
   */
  protected $domainSuffix;

  /**
   * ResponseKeyBase constructor.
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
    $this->dipasConfig = $container->get('dipasconfig.api');
    $this->entityTypeManager = $container->get('entity_type.manager');
    $this->serializer = $container->get('serializer');
    $this->database = $container->get('database');
    $this->languageManager = $container->get('language_manager');
    $this->moduleHandler = $container->get('module_handler');

    if ($this->moduleHandler->moduleExists('domain')) {
      $domainNegotiator = $container->get('domain.negotiator');
      $this->domainModulePresent = TRUE;
      $this->activeDomain = $domainNegotiator->getActiveDomain();

      if ($this->activeDomain !== NULL && !$this->activeDomain->isDefault()) {
        $this->isSubdomain = TRUE;
        $this->domainSuffix = sprintf(':%s', $this->activeDomain->id());
      }
      else {
        $this->isSubdomain = FALSE;
        $this->domainSuffix = '';
      }
    }
    else {
      $this->domainModulePresent = FALSE;
      $this->isSubdomain = FALSE;
      $this->domainSuffix = '';
    }

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
  public function getCookies() {
    return [];
  }

  /**
   * Returns a list of content image styles with their configured width.
   *
   * @return array
   *   An array of content images styles, keyed by their image style name
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
  public static function postProcessResponse(array $responsedata) {
    return $responsedata;
  }

  /**
   * {@inheritdoc}
   */
  final public function getCacheTags() {
    $commonCacheTags = ['dipasRestEndpoint', "dipasRestEndpoint:{$this->pluginDefinition['id']}"];
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
      'maintenanceMessage' => str_replace('@site', $this->dipasConfig->get('ProjectInformation/site_name'), $this->configFactory->get('system.maintenance')->get('message')),
    ];
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
