<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Service;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigValueException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Url;
use Drupal\masterportal\Entity\MasterportalInstanceInterface;
use Drupal\masterportal\PluginSystem\PluginManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class Masterportal.
 *
 * The "renderer" service for the Masterportal integration.
 *
 * @package Drupal\masterportal\Service
 */
class Masterportal implements MasterportalInterface {

  /**
   * Prefix to use for cache ids.
   */
  const CACHE_ID_PREFIX = 'masterportal';

  /**
   * Options to pass to the json_encode function.
   */
  const JSON_OUTPUT_OPTIONS = JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES;

  /**
   * The configuration object.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Custom logging channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannel
   */
  protected $logger;

  /**
   * Custom replacement service for module variables.
   *
   * @var MasterportalTokenServiceInterface
   */
  protected $tokenService;

  /**
   * The storage for nodes.
   *
   * @var EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * The storage for taxonomy terms.
   *
   * @var EntityStorageInterface
   */
  protected $termStorage;

  /**
   * The storage for uploaded files.
   *
   * @var EntityStorageInterface
   */
  protected $fileStorage;

  /**
   * Cache service provided by Drupal.
   *
   * @var CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * The currently processed request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * Custom layer service.
   *
   * @var LayerServiceInterface
   */
  protected $layerService;

  /**
   * Custom plugin manager for config sections.
   *
   * @var PluginManagerInterface
   */
  protected $instanceConfigSectionManager;

  /**
   * Custom plugin manager for layer plugins.
   *
   * @var \Drupal\masterportal\PluginSystem\PluginManagerInterface
   */
  protected $layerPluginManager;

  /**
   * Custom plugin manager for wfs layer styles.
   *
   * @var \Drupal\masterportal\PluginSystem\PluginManagerInterface
   */
  protected $wfsStylePluginManager;

  /**
   * Masterportal constructor.
   *
   * @param \Drupal\masterportal\Service\MasterportalConfigInterface $config
   *   The config service of the Masterportal.
   * @param LoggerChannel $logger
   *   Custom logger channel.
   * @param MasterportalTokenServiceInterface $token_service
   *   Custom service to replace tokens with their respective dynamic values.
   * @param EntityTypeManagerInterface $entity_type_manager
   *   Drupal's entity type manager service.
   * @param CacheBackendInterface $cache_backend
   *   Drupal's data cache service.
   * @param RequestStack $request_stack
   *   Symfonys request stack.
   * @param LayerServiceInterface $layer_service
   *   Custom layer service.
   * @param PluginManagerInterface $instance_config_section_manager
   *   Custom plugin manager for config sections.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *   Thrown if the entity type doesn't exist.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   *   Thrown if the storage handler couldn't be loaded.
   */
  public function __construct(
    MasterportalConfigInterface $config,
    LoggerChannel $logger,
    MasterportalTokenServiceInterface $token_service,
    EntityTypeManagerInterface $entity_type_manager,
    CacheBackendInterface $cache_backend,
    RequestStack $request_stack,
    LayerServiceInterface $layer_service,
    PluginManagerInterface $instance_config_section_manager,
    PluginManagerInterface $wfs_style_plugin_manager,
    PluginManagerInterface $layer_plugin_manager
  ) {
    $this->config = $config;
    $this->logger = $logger;
    $this->tokenService = $token_service;
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->cacheBackend = $cache_backend;
    $this->currentRequest = $request_stack->getCurrentRequest();
    $this->layerService = $layer_service;
    $this->instanceConfigSectionManager = $instance_config_section_manager;
    $this->wfsStylePluginManager = $wfs_style_plugin_manager;
    $this->layerPluginManager = $layer_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function iframe(
    MasterportalInstanceInterface $masterportal_instance,
    $width,
    $aspectratio,
    $zoomLevel = NULL,
    $center = NULL,
    $marker = NULL,
    array $query = []
  ) {

    if (!empty($zoomLevel)) {
      $query['zoomLevel'] = $zoomLevel;
    }

    if (!empty($center) || !empty($marker)) {
      $query['projection'] = 'EPSG:4326';
    }

    if (!empty($center)) {
      $query['center'] = $center;
    }

    if (!empty($marker)) {
      $query['marker'] = $marker;
    }

    if ($this->currentRequest->attributes->has('node')) {
      $query['node'] = $this->currentRequest->attributes->get('node')->id();
    }

    $iframeUrl = preg_replace('~^https?:~i', '', Url::fromRoute(
      'masterportal.fullscreen',
      ['masterportal_instance' => $masterportal_instance->id()],
      [
        'query' => $query,
        'absolute' => TRUE,
      ]
    )->toString());

    $iframe = [
      '#attached' => [
        'library' => ['masterportal/aspectratios'],
      ],
      [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#attributes' => [
          'style' => "width: {$width};",
        ],
        [
          '#type' => 'html_tag',
          '#tag' => 'div',
          '#attributes' => [
            'class' => ['MasterportalIframeWrapper', $aspectratio],
          ],
          'iframe' => [
            '#type' => 'html_tag',
            '#tag' => 'iframe',
            '#attributes' => [
              'class' => ['masterportal'],
              'width' => '100%',
              'height' => '100%',
              'src' => $iframeUrl,
            ],
          ],

        ],
      ],
    ];

    return $iframe;
  }

  /**
   * {@inheritdoc}
   */
  public function createResponse($key, $content_type, $preprocess = NULL, MasterportalInstanceInterface $masterportal_instance = NULL) {

    // Construct the cache ID of the current request.
    $cacheID = sprintf(
      '%s:%s',
      self::CACHE_ID_PREFIX,
      (!empty($cacheID = $this->currentRequest->attributes->get('cacheID')) ? $cacheID : $key)
    );

    // If the current request is instance-sensitive,
    // add the instance id to the cache id.
    if (!empty($masterportal_instance)) {
      $cacheID = sprintf('%s:%s', $cacheID, $masterportal_instance->id());
    }

    // Should any cache contexts be added?
    if (!empty($contexts = $this->currentRequest->attributes->get('cacheContexts'))) {

      // Make sure the cache contexts are in the same order every time they are stated.
      sort($contexts);

      // Container for the context values.
      $context_values = [];

      // Process each context stated and collect the contexts.
      foreach ($contexts as $context) {
        switch ($context) {
          case 'session':
            $context_values[] = $this->currentRequest->getSession()->getId();
            break;
          case 'url':
            $context_values[] = "//{$this->currentRequest->getHttpHost()}{$this->currentRequest->getRequestUri()}";
            break;
        }
      }

      // Suffix the cache ID with the context hash.
      $cacheID .= ':' . hash('sha256', implode(':', $context_values));
    }

    // First, try to get a valid cache for the configuration requested.
    $cache = $this->cacheBackend->get($cacheID);

    // If no valid cache existed, process and cache the data.
    if ($this->currentRequest->query->has('nocache') || $cache === FALSE) {

      // Are there any overwrites that should get passed on?
      $routeObject = $this->currentRequest->get('_route_object');
      if ($routeObject->hasOption('overwrites')) {
        $this->currentRequest->query->add($routeObject->getOption('overwrites'));
      }

      // Get the configured settings.
      $content = $key !== 'JSON' ? $this->config->get($key) : new \stdClass();

      // Throw a 404 if the configuration is unset.
      if ($key !== 'JSON' && empty($content)) {
        $this->logger->warning(
          'No configuration value has been set for %key.',
          ['%key' => $key]
        );
        throw new NotFoundHttpException();
      }

      // If a preprocess function should get called, invoke it (if possible).
      $preprocessCacheTags = [];
      if (!empty($preprocess)) {
        try {
          $content = $this->{$preprocess}($content, $masterportal_instance);

          if (is_array($content)) {
            [$content, $preprocessCacheTags] = $content;
          }
        }
        catch (\Exception $e) {
          // If the preprocess is not callable, we'll just log an error for now.
          $this->logger->error(
            'Preprocess function %function on data key %key called, but the callback does not exist or fails executing. Exception message: %exception',
            [
              '%function' => $preprocess,
              '%key' => $key,
              '%exception' => $e->getMessage(),
            ]
          );
        }
      }

      // Make sure the content is now of type string, else throw an exception.
      // This might be the case when a configured preprocess function could
      // not be called which would otherwise have transformed the data.
      if (!is_string($content)) {
        throw new ConfigValueException(
          sprintf('Content for key %s is not of primitive type string after processing.', $key)
        );
      }

      // Replace possible placeholder variables.
      $content = $this->tokenService->replaceTokens(
        $content,
        array_merge($this->tokenService->getTokens('path'), $this->tokenService->getTokens('layer'))
      );

      if (!$this->currentRequest->attributes->has('cacheExclude')) {
        // Set a new cache with the data processed.
        $cacheNamespace = explode('.', $key);
        $cacheNamespace = array_shift($cacheNamespace);
        $cacheTags = [
          self::CACHE_ID_PREFIX,
          sprintf('%s:%s', self::CACHE_ID_PREFIX, $cacheNamespace),
        ];
        if (!empty($masterportal_instance)) {
          $cacheTags[] = sprintf('%s:instance:%s', self::CACHE_ID_PREFIX, $masterportal_instance->id());
        }
        if (!empty($routingCacheTags = $this->currentRequest->attributes->get('cacheTags'))) {
          array_walk($routingCacheTags, function (&$cacheTag) use ($masterportal_instance) {
            if (strstr($cacheTag, '{masterportal_instance}')) {
              $cacheTag = str_replace('{masterportal_instance}', $masterportal_instance->id(), $cacheTag);
            }
          });
          $cacheTags = array_merge($cacheTags, $routingCacheTags);
        }
        $cacheTags = array_merge($cacheTags, $preprocessCacheTags);
        $this->cacheBackend->set($cacheID, $content, Cache::PERMANENT, $cacheTags);
      }

    }
    // There is a valid cache.
    else {

      // Get the content from the cache.
      $content = $cache->data;

    }

    // Build and return a response object.
    $response = new Response($content);
    $response->headers->set('Content-Type', $content_type);
    return $response;
  }

  /**
   * Preprocess function for the "javascript" route.
   *
   * Prefixes the configured settings object with it's constant
   * declaration and replaces actual configuration values.
   *
   * @param string $content
   *   The configured settings.
   * @param MasterportalInstanceInterface $masterportal_instance
   *   The masterportal instance configuration entity.
   *
   * @return string
   *   The completed javascript configuration object.
   */
  protected function generateJavascriptSettingsObject($content, MasterportalInstanceInterface $masterportal_instance) {
    // Decode the configuration object.
    $content = json_decode($content);

    // Set the Host URL for the remote interface.
    $content->remoteInterface = (object) [
      'postMessageUrl' => $this->currentRequest->query->has('postMessageUrl')
        ? $this->currentRequest->query->get('postMessageUrl')
        : $this->currentRequest->getSchemeAndHttpHost(),
    ];

    // Get the instance settings.
    $settings = $masterportal_instance->get('settings');

    // Process each configuration section.
    foreach ($settings as $configSectionPluginId => $configuration) {

      // Get the plugin definition.
      $configSectionPluginDefinition = $this->instanceConfigSectionManager->getPluginDefinitions($configSectionPluginId);

      // Instantiate the plugin.
      $configSectionPlugin = new $configSectionPluginDefinition['class']($configuration);

      // Let the plugin set it's configuration.
      $configSectionPlugin->injectSectionConfigurationSettings('config.js', $content);

    }

    // uiStyle overridden by URL?
    if ($this->currentRequest->query->has('uiStyle')) {
      $content->uiStyle = $this->currentRequest->query->get('uiStyle');
    }

    // Encode the completed object as JSON.
    $output = json_encode($content, self::JSON_OUTPUT_OPTIONS);

    // Remove the quotation marks around property names.
    $output = preg_replace('~"(\w+?)":~', '\\1:', $output);

    // Return the adapted configuration object as a javascript constant.
    return sprintf('const Config = %s;', $output);
  }

  /**
   * Preprocess function for the "json" route.
   *
   * Replaces actual configuration values and integrates the configured layers.
   *
   * @param string $content
   *   The configured settings.
   * @param MasterportalInstanceInterface $masterportal_instance
   *   The masterportal instance configuration entity.
   *
   * @return string
   *   The completed json configuration object.
   */
  protected function generateJsonSettingsObject($content, MasterportalInstanceInterface $masterportal_instance) {
    // Get the instance settings.
    $settings = $masterportal_instance->get('settings');

    // Remember if any plugin registers post-composition hooks.
    $postCompositionPlugins = [];

    // Process each configuration section.
    foreach ($settings as $configSectionPluginId => $configuration) {

      // Get the plugin definition.
      $configSectionPluginDefinition = $this->instanceConfigSectionManager->getPluginDefinitions($configSectionPluginId);

      // Instantiate the plugin.
      /* @var \Drupal\masterportal\PluginSystem\InstanceConfigSectionInterface $configSectionPlugin */
      $configSectionPlugin = new $configSectionPluginDefinition['class']($configuration);

      // Let the plugin set it's configuration.
      $configSectionPlugin->injectSectionConfigurationSettings('config.json', $content);

      // Is there a post-composition hook present?
      if ($configSectionPlugin->hasPostCompositionHook()) {
        $postCompositionPlugins[] = $configSectionPlugin;
      }

    }

    // Process each registered post-composition hook.
    foreach ($postCompositionPlugins as $configSectionPlugin) {
      $configSectionPlugin->postCompositionHook('config.json', $content);
    }

    // Return the adapted configuration object.
    return json_encode($content, self::JSON_OUTPUT_OPTIONS);
  }

  /**
   * Preprocess function for the "layerdefinitions" route.
   *
   * Enriches the custom layer definitions with the ones provided by FME.
   *
   * @param array $content
   *   The configured settings.
   * @param MasterportalInstanceInterface $masterportal_instance
   *   The masterportal instance configuration entity.
   *
   * @return array
   *   The complete layer definitions in JSON format and the accompanying cache tags.
   */
  protected function enrichLayerDefinitions(array $content, MasterportalInstanceInterface $masterportal_instance = NULL) {
    $cache_tags = [];
    if (empty($masterportal_instance)) {
      // Return all existing layers and their cache tags.
      $layerDefinitions = $this->layerService->getDefinitions();
      $cache_tags = array_merge(
        $cache_tags,
        array_map(
          function ($layer_id) {
            return sprintf('%s:layer:%s', self::CACHE_ID_PREFIX, $layer_id);
          },
          array_keys($layerDefinitions)
        )
      );
      return [
        json_encode(array_values($layerDefinitions), self::JSON_OUTPUT_OPTIONS),
        $cache_tags,
      ];
    }
    else {
      $allLayerIds = $masterportal_instance->getAllLayerIdsInUse();
      $cache_tags = array_merge(
        $cache_tags,
        array_map(
          function ($layer_id) {
            return sprintf('%s:layer:%s', self::CACHE_ID_PREFIX, $layer_id);
          },
          array_values($allLayerIds)
        )
      );
      $layerdefinitionsNeeded = [];
      foreach ($allLayerIds as $id) {
        // Get the basic layer definition.
        if ($layerdefinition = $this->layerService->getLayerDefinition($id)) {

          // Make sure that all possible query parameters get passed to the layer url.
          $layerdefinition->url = Url::fromUri(
            $layerdefinition->url,
            [
              'query' => $this->currentRequest->query->all(),
            ]
          )->toUriString();

          // Add the processed layerdefinition to the definitions array.
          $layerdefinitionsNeeded[] = $layerdefinition;

        }

      }
      return [
        json_encode($layerdefinitionsNeeded, self::JSON_OUTPUT_OPTIONS),
        $cache_tags,
      ];
    }
  }

  /**
   * Preprocess function for the "layerstyles" route.
   *
   * Generates a JSON feed of layer styles.
   *
   * @param array $content
   *   The configured settings.
   *
   * @return array
   *   The complete style definitions in JSON format and the accompanying cache tags.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   *   If the complex data structure is unset and no item can be created.
   */
  protected function generateLayerStyles(array $content) {
    // Container to hold all style definitions.
    $styles = [];

    // Container to hold all cache tags.
    $cacheTags = [];

    // Get all style plugins
    $plugins = $this->wfsStylePluginManager->getPluginDefinitions();

    // Loop over all plugins found and fetch the style definitions.
    foreach ($plugins as $plugin) {
      /* @var \Drupal\masterportal\PluginSystem\LayerStylePluginInterface $instance */
      $instance = new $plugin['class'];

      // Get the plugin's style definition.
      $styles[] = $instance->getStyleObject();

      $cacheTags = array_merge($cacheTags, $instance->getCacheTags());
    }

    // Preprocess custom defined styles.
    // Cycle over each defined style and create a single style array.
    foreach ($content['layerstyles'] as $styleDefinition) {

      // Basic array holding the processed style information.
      $style = [
        'styleId' => $styleDefinition['styleId'],
        'rules' => [],
      ];

      // Throw away the style id as it's already processed.
      unset($styleDefinition['styleId']);

      // Process each defined rule.
      foreach ($styleDefinition as $ruleDefinition) {

        // Create a rule array holding the rule definition.
        $style['rules'][] = [
          'conditions' => [],
          'styles' => [],
        ];

        // This is the target variable all definition information will be inserted in.
        $ruleTarget = &$style['rules'][count($style['rules'])-1];

        // Process each stated rule condition.
        foreach ($ruleDefinition['conditions'] as $conditionDefinition) {

          // Condition names may contain a dot syntax to adress nested properties.
          $conditionTarget = &$ruleTarget['conditions'];
          $conditionProperty = explode('.', $conditionDefinition['property_name']);

          // Make sure to create the stated property path as a nested array structure.
          while ($propertyPath = array_shift($conditionProperty)) {
            $conditionTarget[$propertyPath] = [];
            $conditionTarget = &$conditionTarget[$propertyPath];
          }
          $conditionTarget = $conditionDefinition['property_value'];
        }

        // Process each style attribute.
        foreach ($ruleDefinition['styles'] as $styleDefinition) {
          $propertyValue = $styleDefinition['property_is_json']
            ? json_decode($styleDefinition['property_value'])
            : $styleDefinition['property_value'];
          $ruleTarget['styles'][$styleDefinition['style_property']] = $propertyValue;
        }

      }

      // Insert the readily processed custom style.
      $styles[] = $style;
    }

    // Generate the complete style definition and return it in JSON format
    // as well as the cache tags.
    return [json_encode($styles, self::JSON_OUTPUT_OPTIONS), $cacheTags];
  }

  /**
   * Preprocess function for the "services" route.
   *
   * Fetches the contents of the services.json and returns it.
   *
   * @param string $content
   *   The configured file path.
   *
   * @return string
   *   The complete settings in JSON format.
   */
  protected function generateServicesJson($content) {
    if ($this->tokenService->containsTokens($content)) {
      $this->tokenService->setFileSystemTokenReplacement(TRUE);
      $content = $this->tokenService->replaceTokens($content);
      $content = realpath(sprintf('%s/%s', DRUPAL_ROOT, $content));
    }
    return file_get_contents($content);
  }

  /**
   * Renders the layer data of a GeoJSON layer provided by a layer plugin.
   *
   * @param string $pluginId
   *   The plugin id to render.
   *
   * @return array
   *   The readily json-encoded layer data and the associated cache tags.
   */
  protected function renderLayer() {
    // Get the plugin id from the URL
    $pluginId = $this->currentRequest->attributes->get('layerid');

    // Get the plugin definition.
    /* @var array $pluginDefinition */
    $pluginDefinition = $this->layerPluginManager->getPluginDefinitions($pluginId);

    // Instantiate the plugin.
    /* @var \Drupal\masterportal\PluginSystem\LayerPluginInterface $pluginInstance */
    $pluginInstance = new $pluginDefinition['class']($this->currentRequest);

    // Get the plugin data.
    $pluginData = $pluginInstance->getGeoJSONFeatures();

    // Complete the GeoJSON.
    $geojson = (object) [
      'type' => 'FeatureCollection',
      'features' => [],
    ];
    foreach ($pluginData as $feature) {
      $geojson->features[] = $feature->getFeature();
    }

    // Generate the complete style definition and return it in JSON format.
    return [json_encode($geojson, self::JSON_OUTPUT_OPTIONS), $pluginInstance->getCacheTags()];
  }

}
