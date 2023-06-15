<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Service;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\File\Exception\FileNotExistsException;
use Drupal\Core\File\Exception\InvalidStreamWrapperException;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Url;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\FileRepositoryInterface;
use Drupal\masterportal\DomainAwareTrait;
use Drupal\masterportal\LayerDefinition;
use Drupal\masterportal\PluginSystem\PluginManagerInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class LayerService.
 *
 * Provides functions to deal with layers in a consistent way.
 *
 * @package Drupal\masterportal\Service
 */
class LayerService implements LayerServiceInterface {

  use DomainAwareTrait;

  /**
   * The configuration key holding the layer configuration.
   */
  const LAYERCONFIGURATIION_KEY = 'Layerconfiguration';

  /**
   * The file system path under which the static layer definitions file should be stored.
   *
   * Must be variable since it's going to be passed by reference.
   */
  protected $LAYERDEFINITIONS_FILESYSTEM_DIRECTORY = 'public://services-json';

  /**
   * The file name for the local cache file of the layerdefinitions file.
   */
  const LAYERDEFINITIONS_FILENAME = 'services-internet.json';

  /**
   * To ensure consistent naming of the static definitions array.
   */
  const LAYERDEFINITIONS = 'layerdefinitions';

  /**
   * To ensure consistent naming of the static grouping array.
   */
  const GROUPEDLAYERCONTAINER = 'grouped_layers';

  /**
   * To ensure consistent naming of the static layer array.
   */
  const LAYERCONTAINER = 'layercontainer';

  /**
   * The configuration object.
   *
   * @var array
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
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * Custom plugin manager service for layer plugins.
   *
   * @var \Drupal\masterportal\PluginSystem\PluginManagerInterface
   */
  protected $layerPluginManager;

  /**
   * Symfonys HTTP service.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Drupal's key-value storage service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Drupal's cache tag invalidator service.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheTagInvalidator;

  /**
   * Custom instance service.
   *
   * @var \Drupal\masterportal\Service\InstanceServiceInterface
   */
  protected $instanceService;

  /**
   * Contains a string with a subdomain suffix for state variables.
   *
   * @var string
   */
  protected $domainSuffix;

  /**
   * File System Manager
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Drupal's file handler service.
   *
   * @var \Drupal\file\FileRepositoryInterface
   */
  protected $fileRepository;

  /**
   * LayerService constructor.
   *
   * @param \Drupal\masterportal\Service\MasterportalConfigInterface $config
   *   The Masterportal configuration service.
   * @param \Drupal\Core\Logger\LoggerChannel $logger
   *   Custom logger channel.
   * @param MasterportalTokenServiceInterface $token_service
   *   Custom service to replace tokens with their respective dynamic values.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The currently processed request.
   * @param \Drupal\masterportal\PluginSystem\PluginManagerInterface $layer_plugin_manager
   *   Custom plugin manager service for layer plugins.
   * @param \GuzzleHttp\ClientInterface $http_client
   *   Symfonys HTTP client.
   * @param \Drupal\Core\State\StateInterface $state
   *   Drupal's key-value storage service.
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cache_tag_invalidator
   *   Drupal's cache tag invalidator service.
   * @param \Drupal\masterportal\Service\InstanceServiceInterface $instance_service
   *   Custom instance service.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   Drupal's file system manager.
   * @param \Drupal\file\FileRepositoryInterface $file_repository
   *   Drupal's file handler service.
   */
  public function __construct(
    MasterportalConfigInterface $config,
    LoggerChannel $logger,
    MasterportalTokenServiceInterface $token_service,
    RequestStack $request_stack,
    PluginManagerInterface $layer_plugin_manager,
    ClientInterface $http_client,
    StateInterface $state,
    CacheTagsInvalidatorInterface $cache_tag_invalidator,
    InstanceServiceInterface $instance_service,
    FileSystemInterface $file_system,
    FileRepositoryInterface $file_repository
  ) {
    $this->logger = $logger;
    $this->tokenService = $token_service;
    $this->layerPluginManager = $layer_plugin_manager;
    $this->httpClient = $http_client;
    $this->state = $state;
    $this->cacheTagInvalidator = $cache_tag_invalidator;
    $this->instanceService = $instance_service;
    $this->fileSystem = $file_system;
    $this->fileRepository = $file_repository;

    $this->currentRequest = $request_stack->getCurrentRequest();
    $this->config = $config->get(self::LAYERCONFIGURATIION_KEY);
    $this->domainSuffix = sprintf(':%s', $this->getActiveDomain());
  }

  /**
   * Initializes the static layer definitions array.
   *
   * @param string $key
   *   The name of the data requested.
   *
   * @return mixed|false
   *   The requested data or FALSE if non-existent.
   */
  protected function getData($key) {
    if (
      !in_array(
        $key,
        [
          self::LAYERDEFINITIONS,
          self::LAYERCONTAINER,
          self::GROUPEDLAYERCONTAINER,
        ]
      )
    ) {
      return FALSE;
    }

    ${self::LAYERDEFINITIONS} = &drupal_static(self::LAYERDEFINITIONS, []);
    ${self::LAYERCONTAINER} = &drupal_static(self::LAYERCONTAINER, []);
    ${self::GROUPEDLAYERCONTAINER} = &drupal_static(self::GROUPEDLAYERCONTAINER, []);

    if (!($initialized = &drupal_static('initialized', FALSE))) {

      // Fetch the FME layer definitions and decode them.
      $static_layers = $this->getStaticLayerDefinitions();

      // Discover all layer plugins providing layers.
      $plugin_layers = $this->getPluginLayerDefinitions();

      // Decode potential custom layer definitions (and make sure it's an array).
      $custom_layer_definitions = $this->getCustomLayerDefinitions();

      // Merge all definitions.
      $layerdefinitions = array_merge($static_layers, $plugin_layers, $custom_layer_definitions);

      // Now transform this array so it is keyed by the layer id.
      $keyedById = [];
      foreach ($layerdefinitions as $definition) {
        $keyedById[$definition->id] = $definition;
      }

      // Store the definitions in the static container.
      ${self::LAYERDEFINITIONS} = $keyedById;

      // Prepare grouping of layers.
      $layergroup = [];

      // Cycle over each definition.
      foreach (${self::LAYERDEFINITIONS} as $definition) {
        // Transform it into a LayerDefinition object.
        $layer = new LayerDefinition($definition);

        // Store the definition object into the static variable.
        ${self::LAYERCONTAINER}[$layer->getId()] = $layer;

        // Collect all layers with the same origin in a
        // separate array for grouping.
        if (!isset($layergroup[$layer->getUrl()])) {
          $layergroup[$layer->getUrl()] = [];
        }
        $layergroup[$layer->getUrl()][$layer->getId()] = $layer;
      }

      // Processing of layer grouping continues.
      // Filter out all "grouped" layer indexes holding just one layer.
      $layergroup = array_filter(
        $layergroup,
        function ($group) {
          return count($group) > 1;
        }
      );

      // Now process the remaining groups and group them with
      // identical metadata ids.
      foreach ($layergroup as $url => &$group) {

        $metadataIds = [];
        foreach ($group as $layer) {

          // Get the decoded layer data to extract the metadata id.
          /* @var LayerDefinition $layer */
          $layerData = $layer->getDefinition();

          // Ignore layers without a metadata id (md_id is contained
          // within the datasets).
          if (isset($layerData->datasets) && is_array($layerData->datasets)) {

            // Iterate over each defined dataset.
            foreach ($layerData->datasets as $dataset) {

              // Take the first md_id found.
              if (isset($dataset->md_id)) {

                if (!isset($metadataIds[$dataset->md_id])) {
                  $metadataIds[$dataset->md_id] = [];
                }
                $metadataIds[$dataset->md_id][] = $layer;
                break;

              }
            }
          }

        }

        // Now that we have grouped all layers with a metadata id,
        // filter out metadata groups with just one layer.
        $metadataIds = array_filter(
          $metadataIds,
          function ($group) {
            return count($group) > 1;
          }
        );

        // Now push the groups with content to the static container.
        if (count($metadataIds)) {
          ${self::GROUPEDLAYERCONTAINER}[] = $metadataIds;
        }

      }

      // Mark the definitions as initialized.
      $initialized = TRUE;
    }

    // Return the data requested.
    return $$key;
  }

  /**
   * {@inheritdoc}
   */
  public function getStaticLayerDefinitions($configured_path = NULL) {
    if (is_null($configured_path)) {
      $configured_path = $this->config['static_layer_definitions'];
    }

    if (preg_match('~^https?://~i', $configured_path)) {
      // This is a remote file.
      try {
        $result = $this->httpClient->request('GET', $configured_path);
        $response = $result->getBody()->getContents();

        if (
          $result->getStatusCode() === 200 &&
          !is_null($this->decodeStaticLayerDefinitions($response))
        ) {
          $this->logger->info('Download of static layer definition file successful.');
        }

        $static_layers = $response;

        // Save the data to a file
        try {
          $this->saveFile($static_layers);
        }
        catch (InvalidStreamWrapperException $e) {
          $this->logger->error('The file directory could not be created. Reason: ' . $e->getMessage());
          return [];
        }
        catch (EntityStorageException $e) {
          $this->logger->error('The file could not be saved. Reason: ' . $e->getMessage());
          return [];
        }
      }
      catch (RequestException $e) {
        $this->logger->warning(sprintf(
          'Download failed, trying to use local file instead. HTTP STATUS CODE: %d. Reason: %s.',
          isset($result) ? $result->getStatusCode() : 0,
          $e->getMessage()
        ));

        try {
          $static_layers = $this->readFile();
        }
        catch (FileNotExistsException $e) {
          $this->logger->error($e->getMessage());
          return [];
        }
      }
    }
    else {
      // This is a file within the server's file system.
      if ($this->tokenService->containsTokens($configured_path)) {
        // This is a file relative to the Drupal root.
        $this->tokenService->setFileSystemTokenReplacement(TRUE);
        $configured_path = $this->tokenService->replaceTokens($configured_path);
        $filepath = DRUPAL_ROOT . $configured_path;
      }
      else {
        // This is a file with a path somewhere in the server's file system.
        $filepath = realpath($configured_path);
      }
      $static_layers = file_get_contents($filepath);
    }

    return $this->decodeStaticLayerDefinitions($static_layers);
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginLayerDefinitions() {
    $plugin_layers = [];
    $pluginDefinitions = $this->layerPluginManager->getPluginDefinitions();
    foreach ($pluginDefinitions as $pluginId => $pluginDefinition) {
      /* @var \Drupal\masterportal\PluginSystem\LayerPluginInterface $pluginInstance */
      $pluginInstance = new $pluginDefinition['class']($this->currentRequest);

      $layerdefinition = $pluginInstance->getLayerDefinition();
      $layerdefinition->id = $pluginId;
      $layerdefinition->name = $pluginDefinition['title']->__toString();
      $layerdefinition->typ = "GeoJSON";
      $layerdefinition->format = "text/html; charset=utf-8";
      $layerdefinition->url = Url::fromRoute(
        'masterportal.layerplugin',
        ['layerid' => $pluginId],
        ['absolute' => TRUE]
      )->toString();

      $plugin_layers[] = $layerdefinition;
    }

    return $plugin_layers;
  }

  /**
   * {@inheritdoc}
   */
  public function getCustomLayerDefinitions() {
    $custom_layer_definitions = !empty($this->config['custom_layers'])
      ? json_decode($this->tokenService->replaceTokens($this->config['custom_layers']))
      : [];
    if (!is_array($custom_layer_definitions)) {
      $custom_layer_definitions = [$custom_layer_definitions];
    }
    return $custom_layer_definitions;
  }

  /**
   * Get the cache tags corresponding to layer definitions provided.
   *
   * @param array $definitions
   *   The layer definitions.
   *
   * @return string[]
   *   The cache tags for the layer definitions provided.
   */
  protected function getLayerCacheTags(array $definitions) {
    return array_map(
      function (\stdClass $layer) {
        return sprintf('%s:layer:%s', Masterportal::CACHE_ID_PREFIX, $layer->id);
      },
      $definitions
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getLayerDefinition($layerid) {
    /* @var LayerDefinition[] $layercontainer */
    $layercontainer = $this->getData(self::LAYERCONTAINER);
    return isset($layercontainer[$layerid])
      ? $layercontainer[$layerid]->getDefinition()
      : '';
  }

  /**
   * {@inheritdoc}
   */
  public function getLayerOptions() {

    // Provide an options container.
    $options = [];

    // Get the grouped layers first.
    $groups = $this->getData(self::GROUPEDLAYERCONTAINER);

    // Container for layer ids that have been grouped.
    $groupedIds = [];

    // Generate options for these grouped layers.
    foreach ($groups as $grouping) {
      foreach ($grouping as $metadataId => $group) {

        // Extract all layer ids from this group.
        $layerids = array_map(
          function (LayerDefinition $layer) {
            return $layer->getId();
          },
          $group
        );

        // Extract all layer metadata names from this group.
        $layernames = array_map(
          function (LayerDefinition $layer) {
            return $layer->getDefinition()->datasets[0]->md_name;
          },
          $group
        );

        // Unify these labels.
        $layernames = array_unique($layernames);

        // Generate an option for this group.
        $options[json_encode($layerids)] = sprintf(
          '%s (Layer-IDs: %s)',
          implode(' / ', $layernames),
          implode(', ', $layerids)
        );

        // Remember the ids already used for an option to skip
        // these in their ungrouped form.
        $groupedIds = array_merge($groupedIds, $layerids);

      }
    }

    // Now, add all layer options that have not been grouped before.
    foreach ($this->getDefinitions() as $layerid => $layer) {
      $options[$layerid] = sprintf('%s (Layer-ID: %s)', $layer->name, $layerid);
    }

    // Sort the options alphabetically.
    asort($options, SORT_REGULAR);

    // Return the options.
    return $options;

  }

  /**
   * {@inheritdoc}
   */
  public function getDefinitions() {
    $layerdefinitions = $this->getData(self::LAYERDEFINITIONS);
    return $layerdefinitions;
  }

  /**
   * {@inheritdoc}
   */
  public function getLayerNameForCompositeIds(array $ids) {
    $layernames = [];
    foreach ($ids as $id) {
      if (!empty($definition = $this->getLayerDefinition($id))) {
        $layernames[] = $definition->datasets[0]->md_name;
      }
    }
    $layernames = array_unique($layernames);
    return implode(' / ', $layernames);
  }

  /**
   * {@inheritdoc}
   */
  public function checkLayerDefinitions($configured_path) {
    $layer_definitions = $this->getStaticLayerDefinitions($configured_path);

    if (!is_array($layer_definitions)) {
      return 'The given definition file does not contain an array of layer definitions.';
    }

    foreach ($layer_definitions as $definition) {
      if (!$definition instanceof \stdClass) {
        return 'The definition file contains an invalid structure.';
      }
      foreach (['id', 'name', 'url'] as $needed_property) {
        if (!property_exists($definition, $needed_property)) {
          return sprintf('The definitions are missing vital data (%s).', $needed_property);
        }
      }
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function checkForLayerChanges($configured_path = NULL) {
    $missing_layers = [];

    foreach (['static', 'plugin', 'custom'] as $key) {

      // Preparatory work for static file checks.
      if ($key === 'static') {
        if (is_null($configured_path)) {
          $configured_path = $this->config['static_layer_definitions'];
        }
        $state_key = sprintf('masterportal:modifiedState:staticLayerDefinitions%s', $this->getActiveDomain());
        $versionChecked = $this->state->get($state_key);
        $lastModified = $this->getLastModifiedTime($configured_path);
        if (
          !is_null($versionChecked) &&
          $versionChecked['configured_path'] === $configured_path &&
          $lastModified !== 0 &&
          $versionChecked['mtime'] === $lastModified
        ) {
          continue;
        }
        $this->state->set(
          $state_key,
          [
            'configured_path' => $configured_path,
            'mtime' => $lastModified,
          ]
        );
      }

      // Determine the actual contained cache tags.
      $new_cache_tags = $this->getLayerCacheTags(
        $this->{sprintf('get%sLayerDefinitions', ucfirst($key))}($configured_path)
      );

      // Determine the key under which these get stored in Drupal's state.
      $state_key = sprintf('masterportal:layerdefinitions:%s%s', $key, $this->getActiveDomain());

      // Check, if there were previously stored tags.
      if (!empty($stored_cache_tags = $this->state->get($state_key, []))) {
        // Keep track of missing tags.
        $missing_layers = array_merge(
          $missing_layers,
          array_diff($stored_cache_tags, $new_cache_tags)
        );
      }

      // Store the new tags.
      $this->state->set($state_key, $new_cache_tags);

    }

    if (!empty($missing_layers)) {
      // Invalidate all missing cache tags.
      $this->cacheTagInvalidator->invalidateTags($missing_layers);

      // Check configured Masterportal instances.
      array_walk(
        $missing_layers,
        function (&$cache_tag) {
          [,,$cache_tag] = explode(':', $cache_tag);
        }
      );
      $this->instanceService->checkInstancesForRemovedLayers($missing_layers);
    }

  }

  /**
   * Checks the modification timestamp of a given file.
   *
   * @param string $configured_path
   *   The file location.
   *
   * @return int
   */
  protected function getLastModifiedTime($configured_path) {
    if (preg_match('~^https?://~i', $configured_path)) {
      try {
        $result = $this->httpClient->request('HEAD', $configured_path);
        $lastModified = strtotime($result->getHeader('Last-Modified')[0]);
      }
      catch (RequestException $e) {
        $lastModified = 0;
      }
    }
    else {
      // This is a file within the server's file system.
      if ($this->tokenService->containsTokens($configured_path)) {
        // This is a file relative to the Drupal root.
        $this->tokenService->setFileSystemTokenReplacement(TRUE);
        $configured_path = $this->tokenService->replaceTokens($configured_path);
        $filepath = DRUPAL_ROOT . $configured_path;
      }
      else {
        // This is a file with a path somewhere in the server's file system.
        $filepath = realpath($configured_path);
      }
      $lastModified = filemtime($filepath);
    }
    return $lastModified;
  }

  /**
   * Saves the remote data to the file system
   *
   * @param string $data
   *   The file content to save to the file system.
   *
   * @return \Drupal\file\FileInterface
   *   The newly created file entity.
   *
   * @throws \Drupal\Core\File\Exception\InvalidStreamWrapperException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function saveFile($data) {
    if (
      !$this->fileSystem->prepareDirectory(
        $this->LAYERDEFINITIONS_FILESYSTEM_DIRECTORY,
        FileSystemInterface::CREATE_DIRECTORY
      )
    ) {
      throw new InvalidStreamWrapperException();
    }

    return $this->fileRepository->writeData(
      $data,
      sprintf(
        "%s/%s",
        $this->LAYERDEFINITIONS_FILESYSTEM_DIRECTORY,
        self::LAYERDEFINITIONS_FILENAME
      ),
      FileSystemInterface::EXISTS_REPLACE
    );
  }

  /**
   * Reads the local static layer definitions file cache.
   *
   * @return string
   *   The contents of the static layer definitions file cache.
   *
   * @throws \Drupal\Core\File\Exception\FileNotExistsException
   */
  protected function readFile() {
    $file_path = sprintf(
      "%s/%s",
      $this->LAYERDEFINITIONS_FILESYSTEM_DIRECTORY,
      self::LAYERDEFINITIONS_FILENAME
    );

    // Check if the file exists and is readable
    if (is_readable($file_path)) {
      return file_get_contents($file_path);
    }
    else {
      throw new FileNotExistsException('The file ' . $file_path . ' does not exist on the filesystem or is not readable.');
    }
  }

  /**
   * Decodes the JSON contained in the static definitions file.
   *
   * @param string $contents
   *   The contents of the static layer definitions file.
   *
   * @return array
   */
  protected function decodeStaticLayerDefinitions($contents) {
    $bom = pack('H*', 'EFBBBF');
    $contents = preg_replace("~^$bom~", '', $contents);

    return json_decode($contents);
  }

}
