<?php

namespace Drupal\dipas_stories\EventSubscriber;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Http\RequestStack;
use Drupal\Core\Url;
use Drupal\dipas\Exception\MalformedRequestException;
use Drupal\dipas_stories\LoadEntityTrait;
use Drupal\dipas_stories\MapSettingsTrait;
use Drupal\dipas_stories\PluginSystem\MasterportalMapFeaturePluginManager;
use Drupal\dipas_stories\StoryRelationHandlerTrait;
use Drupal\masterportal\EnsureObjectStructureTrait;
use Drupal\masterportal\Event\MasterportalCacheEvents;
use Drupal\masterportal\Event\MasterportalCacheEventsInterface;
use Drupal\masterportal\Event\MasterportalConfigEventInterface;
use Drupal\masterportal\Event\MasterportalConfigEvents;
use Drupal\masterportal\Event\MasterportalResponseEventInterface;
use Drupal\masterportal\Event\MasterportalResponseEvents;
use Drupal\masterportal\PluginSystem\ToolPluginManagerInterface;
use Drupal\masterportal\Service\LayerServiceInterface;
use Drupal\masterportal\Service\Masterportal;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MasterportalEvents implements EventSubscriberInterface {

  use MapSettingsTrait,
    StoryRelationHandlerTrait,
    LoadEntityTrait,
    EnsureObjectStructureTrait;

  /**
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\masterportal\PluginSystem\ToolPluginManagerInterface
   */
  protected $toolPluginManager;

  /**
   * @var \Drupal\masterportal\Service\LayerServiceInterface
   */
  protected $layerService;

  /**
   * @var \Drupal\dipas_stories\PluginSystem\MasterportalMapFeaturePluginManager
   */
  protected $mapFeaturePluginManager;

  /**
   * @var \Drupal\dipas_stories\PluginSystem\MasterportalMapFeaturePluginInterface[]
   */
  protected $mapFeaturePlugins = [];

  /**
   * @param \Drupal\Core\Http\RequestStack $request_stack
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\masterportal\PluginSystem\ToolPluginManagerInterface $tool_plugin_manager
   * @param \Drupal\masterportal\Service\LayerServiceInterface $layer_service
   * @param \Drupal\dipas_stories\PluginSystem\MasterportalMapFeaturePluginManager $map_feature_plugin_manager
   */
  public function __construct(
    RequestStack $request_stack,
    EntityTypeManagerInterface $entity_type_manager,
    ToolPluginManagerInterface $tool_plugin_manager,
    LayerServiceInterface $layer_service,
    MasterportalMapFeaturePluginManager $map_feature_plugin_manager
  ) {
    $this->currentRequest = $request_stack->getCurrentRequest();
    $this->entityTypeManager = $entity_type_manager;
    $this->toolPluginManager = $tool_plugin_manager;
    $this->layerService = $layer_service;
    $this->mapFeaturePluginManager = $map_feature_plugin_manager;

    foreach ($this->mapFeaturePluginManager->getDefinitions() as $definition) {
      $this->mapFeaturePlugins[$definition['id']] = [
        'definition' => $definition,
        'instance' => new $definition['class'](),
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      MasterportalConfigEvents::JavascriptCreate => ['onJavascriptCreate'],
      MasterportalConfigEvents::JsonCreate => ['onJsonCreate'],
      MasterportalConfigEvents::LayerdefinitionsCreate => ['onLayerdefinitionCreate'],
      MasterportalCacheEvents::CacheIdCreate => ['onCacheIdCreate'],
      MasterportalCacheEvents::CacheTagsCreate => ['onCacheTagsCreate'],
    ];
  }

  /**
   * Event handler function for MasterportalEvents::JavascriptCreate
   *
   * @param \Drupal\masterportal\Event\MasterportalConfigEventInterface $event
   *
   * @return void
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function onJavascriptCreate(MasterportalConfigEventInterface $event): void {
    if ($event->getMasterportalInstance()->id() === 'default.dipas_story_telling') {
      $jsConfig = $event->getConfiguration();
      $jsConfig->addons[] = 'dipasStorySelector';
      $jsConfig->addons[] = 'dipasAddons-dipasStorySelector';
      $jsConfig->addons[] = 'dataNarrator';
      $jsConfig->addons[] = 'dipasAddons-dataNarrator';
      if ($this->currentRequest->query->has('preview')) {
        $jsConfig->addons[] = 'handle3DParameters';
        $jsConfig->addons[] = 'dipasAddons-handle3DParameters';
      }
      $jsConfig->addons = array_values(array_unique($jsConfig->addons));

      if ($this->currentRequest->query->has('preview')) {
        /**
         * In preview mode, enable all available map feature plugins to act on the configuration.
         */
        foreach ($this->mapFeaturePlugins as $entry) {
          if ($entry['definition']['integrateInPreviewMode']) {
            $entry['instance']->modifyJavaScriptConfiguration($jsConfig, $this->currentRequest);
          }
        }
      }
      else if ($this->currentRequest->query->has('story')) {
        $storyID = $this->determineStoryIDFromStoryUrl();
        $mapSettings = $this->getMapSettingsFromStory('node', $storyID);

        /**
         * Allow enabled map feature plugins to act on the Json configuration
         */
        $enabledMapFeaturePlugins = array_filter(
          $this->mapFeaturePlugins,
          function ($pluginID) use ($mapSettings) {
            return isset($mapSettings->MapFeatures) && in_array($pluginID, $mapSettings->MapFeatures);
          },
          ARRAY_FILTER_USE_KEY
        );

        foreach ($enabledMapFeaturePlugins as $entry) {
          $entry['instance']->modifyJavaScriptConfiguration($jsConfig, $this->currentRequest);
        }
      }

      $event->setConfiguration($jsConfig);
    }
  }

  /**
   * Event handler function for MasterportalEvents::JsonCreate
   *
   * @param \Drupal\masterportal\Event\MasterportalConfigEventInterface $event
   *
   * @return void
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   * @throws \Drupal\dipas\Exception\MalformedRequestException
   */
  public function onJsonCreate(MasterportalConfigEventInterface $event): void {
    if ($event->getMasterportalInstance()->id() === 'default.dipas_story_telling') {
      $jsonSettings = $event->getConfiguration();

      $jsonSettings->Themenconfig = [
        'Hintergrundkarten' => [
          'Layer' => [],
        ],
        'Fachdaten' => [
          'Layer' => [],
        ],
      ];

      if ($this->currentRequest->query->has('preview')) {
        $VisibleLayers = [];
        $BackgroundLayer = [];
        $ForegroundLayer = [];

        foreach (['VisibleLayers', 'BackgroundLayer', 'ForegroundLayer'] as $property) {
          if ($this->currentRequest->query->has($property)) {
            $$property = explode('/', $this->currentRequest->query->get($property));
          }
        }

        foreach ($BackgroundLayer as $item) {
          if ($id = json_decode($item)) {
            $jsonSettings->Themenconfig['Hintergrundkarten']['Layer'][] = [
              'id' => is_array($id) ? array_map(function ($item) { return "$item"; }, $id) : "$id",
              'visibility' => in_array($item, $VisibleLayers),
            ];
          }
        }

        foreach ($ForegroundLayer as $item) {
          if ($id = json_decode($item)) {
            $jsonSettings->Themenconfig['Fachdaten']['Layer'][] = [
              'id' => is_array($id) ? array_map(function ($item) {
                return "$item";
              }, $id) : "$id",
              'visibility' => in_array($item, $VisibleLayers),
            ];
          }
        }

        self::ensureConfigPath($jsonSettings, 'Portalconfig->menu->tools->children');

        $jsonSettings->Portalconfig->menu->tools->children->handle3DParameters = [
          'name' => 'handle3DParameters',
          'isVisibleInMenu' => FALSE,
        ];

        /**
         * In preview mode, enable all available map feature plugins to act on the configuration.
         */
        foreach ($this->mapFeaturePlugins as $entry) {
          if ($entry['definition']['integrateInPreviewMode']) {
            $entry['instance']->modifyJsonConfiguration($jsonSettings, $this->currentRequest);
          }
        }
      }
      elseif ($this->currentRequest->query->has('story')) {
        $storyID = $this->determineStoryIDFromStoryUrl();
        $mapSettings = $this->getMapSettingsFromStory('node', $storyID);
        $stepSettings = $this->getMapSettingsFromFirstStoryStep($storyID);

        foreach (['BackgroundLayer' => 'Hintergrundkarten', 'ForegroundLayer' => 'Fachdaten'] as $source => $target) {
          foreach ($mapSettings->{$source}->selectedLayers as $item) {
            $layerID = json_decode($item);
            $layerID = is_array($layerID) ? array_map(function ($elem) { return "$elem"; }, $layerID) : "$layerID";

            $layer = [
              'id' => $layerID,
              'visibility' => in_array(
                $item,
                is_array($stepSettings->{$source}->visibleLayers)
                  ? $stepSettings->{$source}->visibleLayers
                  : []
              ),
            ];

            if (isset($mapSettings->{$source}->layerProperties[$item])) {
              if (strlen($mapSettings->{$source}->layerProperties[$item]->layerName)) {
                $layer['name'] = $mapSettings->{$source}->layerProperties[$item]->layerName;
                $layer['layerattribution'] = $mapSettings->{$source}->layerProperties[$item]->layerName;
              }

              if (strlen($mapSettings->{$source}->layerProperties[$item]->layerJson)) {
                $layerJson = (array) json_decode(trim($mapSettings->{$source}->layerProperties[$item]->layerJson));
                $layer = array_merge($layer, $layerJson);
              }
            }

            $jsonSettings->Themenconfig[$target]['Layer'][] = $layer;
          }
        }

        // Make sure the story selector tool and the story telling tool get integrated into the configuration
        $dipasStorySelectorTool = $this->toolPluginManager->getPluginDefinitionByConfigProperty('dipasStorySelector');
        $dipasStoryLoaderTool = $this->toolPluginManager->getPluginDefinitionByConfigProperty('dataNarrator');
        $toolsToIntegrate = array_merge(
          [
            'dipasStorySelector' => $dipasStorySelectorTool['class']::getDefaults(),
            'dataNarrator' => $dipasStoryLoaderTool['class']::getDefaults(),
          ],
          (array) $mapSettings->Tools
        );

        // Ensure the story selector tool url is set to the current domain
        $toolsToIntegrate['dipasStorySelector']['storyIndexURL'] = sprintf(
          'http%s://%s%s',
          (int) $this->currentRequest->getPort() === 443 ? 's' : '',
          $this->currentRequest->getHttpHost(),
          Url::fromRoute('dipas_stories.stories')->toString()
        );

        static::ensureConfigPath($jsonSettings, 'Portalconfig->menu->tools->children');

        foreach ($toolsToIntegrate as $configProperty => $defaults) {
          $pluginDefinition = $this->toolPluginManager->getPluginDefinitionByConfigProperty($configProperty);
          $pluginInstance = new $pluginDefinition['class']((array) $defaults);

          $jsonSettings->Portalconfig->menu->tools->children->{$configProperty} = new \stdClass();
          $pluginInstance->injectConfiguration($jsonSettings->Portalconfig->menu->tools->children->{$configProperty});
        }

        /**
         * Allow enabled map feature plugins to act on the Json configuration
         */
        $enabledMapFeaturePlugins = array_filter(
          $this->mapFeaturePlugins,
          function ($pluginID) use ($mapSettings) {
            return isset($mapSettings->MapFeatures) && in_array($pluginID, $mapSettings->MapFeatures);
          },
          ARRAY_FILTER_USE_KEY
        );

        foreach ($enabledMapFeaturePlugins as $entry) {
          $entry['instance']->modifyJsonConfiguration($jsonSettings, $this->currentRequest);
        }

      }
      else {
        throw new MalformedRequestException('The request misses vital information!');
      }

      $event->setConfiguration($jsonSettings);
    }
  }

  /**
   * Event handler function for MasterportalEvents::LayerdefinitionsCreate
   *
   * @param \Drupal\masterportal\Event\MasterportalConfigEventInterface $event
   *
   * @return void
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   * @throws \Drupal\dipas\Exception\MalformedRequestException
   */
  public function onLayerdefinitionCreate(MasterportalConfigEventInterface $event): void {
    if ($event->getMasterportalInstance()->id() === 'default.dipas_story_telling') {
      $layersInUse = [];
      $cache_tags = [];

      if ($this->currentRequest->query->has('preview')) {
        foreach (['BackgroundLayer', 'ForegroundLayer'] as $property) {
          if ($this->currentRequest->query->has($property)) {
            $queryValue = explode('/', trim($this->currentRequest->query->get($property)));

            foreach ($queryValue as $item) {
              $item = json_decode(trim($item));
              $layersInUse = array_merge($layersInUse, is_array($item) ? $item : [$item]);
            }
          }
        }

        $layersInUse = array_unique($layersInUse);
      }
      elseif ($this->currentRequest->query->has('story')) {
        $storyID = $this->determineStoryIDFromStoryUrl();
        $cache_tags[] = sprintf('node:%s', $storyID);
        $mapSettings = $this->getMapSettingsFromStory('node', $storyID);

        foreach (['BackgroundLayer', 'ForegroundLayer'] as $property) {
          $selectedLayers = $mapSettings->{$property}->selectedLayers;

          foreach ($selectedLayers as $item) {
            $item = json_decode(trim($item));
            $layersInUse = array_merge($layersInUse, is_array($item) ? $item : [$item]);
          }
        }

        $layersInUse = array_unique($layersInUse);
      }
      else {
        throw new MalformedRequestException('The request misses vital information!');
      }

      $cache_tags = array_merge($cache_tags, array_map(
        function ($layer_id) {
          return sprintf('%s:layer:%s', Masterportal::CACHE_ID_PREFIX, $layer_id);
        },
        array_values($layersInUse)
      ));

      $layerdefinitionsNeeded = [];

      foreach ($layersInUse as $id) {
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

      $event->setConfiguration($layerdefinitionsNeeded);
      $event->setCacheTags($cache_tags);
    }
  }

  /**
   * Event handler function for MasterportalCacheEvents::CacheIdCreate
   *
   * @param \Drupal\masterportal\Event\MasterportalCacheEventsInterface $event
   *
   * @return void
   */
  public function onCacheIdCreate(MasterportalCacheEventsInterface $event): void {
    if (
      $event->getMasterportalInstance()->id() === 'default.dipas_story_telling'
      && $this->currentRequest->query->has('story')
    ) {
      $cacheID = $event->getConfiguration();
      $cacheID = sprintf('%s:story-%s', $cacheID, $this->determineStoryIDFromStoryUrl());
      $event->setConfiguration($cacheID);
    }
  }

  /**
   * Event handler function for MasterportalCacheEvents::CacheTagsCreate
   *
   * @param \Drupal\masterportal\Event\MasterportalCacheEventsInterface $event
   *
   * @return void
   */
  public function onCacheTagsCreate(MasterportalCacheEventsInterface $event): void {
    if (
      $event->getMasterportalInstance()->id() === 'default.dipas_story_telling'
      && $this->currentRequest->query->has('story')
    ) {
      $cacheTags = $event->getConfiguration();
      $cacheTags[] = sprintf('node:%s', $this->determineStoryIDFromStoryUrl());
      $event->setConfiguration($cacheTags);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityTypeManager(): EntityTypeManagerInterface {
    return $this->entityTypeManager;
  }

  /**
   * Helper function to retrieve the node ID of the story from the given story URL.
   *
   * @return false|string|null
   */
  protected function determineStoryIDFromStoryUrl(): bool|string|null {
    if ($this->currentRequest->query->has('story')) {
      $storyUrl = $this->currentRequest->query->get('story');
      $parsedUrl = UrlHelper::parse($storyUrl);
      $storyID = explode('/', $parsedUrl['path']);

      return array_pop($storyID);
    }

    return FALSE;
  }

}
