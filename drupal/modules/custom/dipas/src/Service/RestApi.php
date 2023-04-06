<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Service;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\dipas\Exception\MalformedRequestException;
use Drupal\dipas\ResponseContent;
use Drupal\masterportal\DomainAwareTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class RestApi.
 *
 * @package Drupal\dipas\Service
 */
class RestApi implements RestApiInterface {
  use DomainAwareTrait;

  /**
   * Options to pass to the json_encode function.
   */
  const JSON_OUTPUT_OPTIONS = JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES;

  /**
   * The DIPAS configuration service.
   *
   * @var \Drupal\dipas\Controller\DipasConfig
   */
  protected $config;

  /**
   * Custom logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Custom response plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $responsePluginManager;

  /**
   * The currently processed request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Drupal's cache service.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Drupal's cache tags invalidation service.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheTagsInvalidator;

  /**
   * Domain sensitive cache context.
   *
   * @var string
   */
  protected $domainCachePrefix;

  /**
   * Custom pds response plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $pdsResponsePluginManager;

  /**
   * Custom cockpitData response plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $cockpitdataResponsePluginManager;

  /**
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * RestApi constructor.
   *
   * @param \Drupal\dipas\Service\DipasConfigInterface $config
   *   Custom config service.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   Custom logger channel.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $response_key_plugin_manager
   *   Custom plugin manager for response plugins.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack object.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Drupal's cache service.
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cache_tags_invalidator
   *   Drupal's cache tags invalidation service.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $pds_response_plugin_manager
   *   Custom plugin manager for pds response plugins.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $cockpitdata_response_plugin_manager
   *   Custom plugin manager for cockpitData response plugins.
   */
  public function __construct(
    DipasConfigInterface $config,
    LoggerChannelInterface $logger,
    PluginManagerInterface $response_key_plugin_manager,
    RequestStack $request_stack,
    CacheBackendInterface $cache,
    CacheTagsInvalidatorInterface $cache_tags_invalidator,
    PluginManagerInterface $pds_response_plugin_manager,
    PluginManagerInterface $cockpitdata_response_plugin_manager,
    ModuleHandlerInterface $module_handler
  ) {
    $this->config = $config;
    $this->logger = $logger;
    $this->responsePluginManager = $response_key_plugin_manager;
    $this->request = $request_stack->getCurrentRequest();
    $this->cache = $cache;
    $this->cacheTagsInvalidator = $cache_tags_invalidator;
    $this->pdsResponsePluginManager = $pds_response_plugin_manager;
    $this->cockpitdataResponsePluginManager = $cockpitdata_response_plugin_manager;
    $this->moduleHandler = $module_handler;

    $this->domainCachePrefix = $this->getActiveDomain();
  }

  /**
   * Validates a token provided if needed and throws an exception if not available/faulty.
   *
   * @param array $pluginDefinition
   *   The definition of the request plugin.
   *
   * @return void
   * @throws \Drupal\dipas\Exception\MalformedRequestException
   *   Exception is thrown if the token was not provided or does not match.
   */
  protected function shieldRequest(array $pluginDefinition) {
    if ($pluginDefinition['shieldRequest']) {
      [$cypher, $security_token, $passphrase, $initvector] = require_once(realpath(__DIR__ . '/../Plugin/ResponseKey/RestApiToken.php'));

      if (
        ($token = $this->request->query->get('token')) &&
        ($decryptedToken = openssl_decrypt($token, $cypher, $passphrase, 0, $initvector)) &&
        count($decryptedToken = explode(":|:", $decryptedToken)) === 2
      ) {
        $tokenTime = (int) $decryptedToken[1];
        $decryptedToken = $decryptedToken[0];

        if ($security_token !== $decryptedToken || time() > ($tokenTime + 5)) {
          throw new MalformedRequestException();
        }
      }
      else {
        throw new MalformedRequestException();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function requestEndpoint($key) {
    // Check, if any other module implements this key before lifting heavy weights.
    $foreignApis = $this->moduleHandler->invokeAll('dipas_api_links');
    if (in_array($key, $foreignApis)) {
      return FALSE;
    }

    $response = new JsonResponse();
    $cacheId = "{$this->domainCachePrefix}/{$key}/{$this->request->attributes->get('id')}" . json_encode($this->request->query->all());

    try {
      $pluginDefinition = $this->responsePluginManager->getDefinition(strtolower($key));

      $this->shieldRequest($pluginDefinition);

      if (
        $pluginDefinition['isCacheable'] &&
        !$this->request->query->has('noCache') &&
        $cache = $this->cache->get($cacheId)
      ) {
        $content = $cache->data;
      }
      elseif (!in_array($this->request->getMethod(), $pluginDefinition['requestMethods'])) {
        $content = new ResponseContent(
          ResponseContent::RESPONSE_STATUS_ERROR,
          'The requested resource cannot be found on this server.',
          404
        );
      }
      else {
        /* @var \Drupal\dipas\PluginSystem\ResponseKeyPluginInterface $plugin */
        $plugin = new $pluginDefinition['class']($pluginDefinition, $this->request, $response);
        try {
          $content = new ResponseContent(
            ResponseContent::RESPONSE_STATUS_SUCCESS,
            $plugin->getResponseData()
          );
          $this->cacheTagsInvalidator->invalidateTags([sprintf('dipasRestEndpoint:%s', $key)]);
          $this->cache->set($cacheId, $content, Cache::PERMANENT, $plugin->getCacheTags());
          if (!empty($cookies = $plugin->getCookies())) {
            foreach ($cookies as $cookie) {
              $response->headers->setCookie($cookie);
            }
          }
        }
        catch (NotFoundHttpException $e) {
          $content = new ResponseContent(
            ResponseContent::RESPONSE_STATUS_ERROR,
            'The requested resource cannot be found on this server.',
            404
          );
        }
        catch (\Exception $e) {
          $this->logger->error("Unexpected error: {$e->getCode()} - {$e->getMessage()}");
          $content = new ResponseContent(
            ResponseContent::RESPONSE_STATUS_ERROR,
            $e->getMessage(),
            ($e->getCode() !== 0 ? $e->getCode() : 500)
          );
        }
      }
    }
    catch (MalformedRequestException $e) {
      $this->logger->warning("Call to endpoint with faulty or without security token: {$key}");
      $content = new ResponseContent(
        ResponseContent::RESPONSE_STATUS_ERROR,
        "Malformed request",
        500
      );
    }
    catch (PluginNotFoundException $e) {
      $this->logger->notice("Undefined endpoint: {$key}");
      $content = new ResponseContent(
        ResponseContent::RESPONSE_STATUS_ERROR,
        "Unknown key: {$key}",
        500
      );
    }

    if (!$content->isError()) {
      $content->updateContent($pluginDefinition['class']::postProcessResponse($content->getRawContent()));
    }

    $response->setData($content->getResponseContent());
    if ($content->isError()) {
      $response->setStatusCode($content->getResponseStatusCode());
    }
    $response->setEncodingOptions(static::JSON_OUTPUT_OPTIONS);
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function requestPDSEndpoint($proj_ID, $type, $contr_ID, $comments) {
    $response = new JsonResponse();

    try {
      $plugin_key = '';

      if ($type === 'none') {
        // ./drupal/dips_pds/projects
        // ./drupal/dips_pds/projects/[proj_ID]
        // request all projects or one single project with ID $proj_ID
        $plugin_key = 'pdsprojectlist';
      }
      else if ($type === 'contributions') {
        // ./drupal/dips_pds/projects/[proj_ID]/contributions
        // ./drupal/dips_pds/projects/[proj_ID]/contributions/[contr_ID]
        // request all contributions of project $proj_ID or a single contribution with ID $contr_ID

        if ($comments === 'comments') {
          // ./drupal/dips_pds/projects/[proj_ID]/contributions/[contr_ID]/comments
          // request ONLY comments on a single contribution with ID $contr_ID
          $plugin_key = 'pdscommentslist';
        }
        else {
          $plugin_key = 'pdscontributionlist';
        }
      }
      else if ($type === 'commentedcontributions') {
        // ./drupal/dips_pds/projects/[proj_ID]/commentedcontributions
        // ./drupal/dips_pds/projects/[proj_ID]/commentedcontributions/[contr_ID]
        // request all contributions of project $proj_ID or a single contribution with ID $contr_ID
        // WITH comments

        $plugin_key = 'pdscontributionlist';
      }
      else if ($type === 'conception_comments') {
        // ./drupal/dips_pds/projects/[proj_ID]/conception_comments
        // request alle comments on conceptions for a project $proj_ID
        $plugin_key = 'pdscommentslist';
      }

      $pluginDefinition = $this->pdsResponsePluginManager->getDefinition($plugin_key);

      /* @var \Drupal\dipas\PluginSystem\ResponseKeyPluginInterface $plugin */
      $plugin = new $pluginDefinition['class']($pluginDefinition, $this->request, $response);
      try {
        $content = new ResponseContent(
            ResponseContent::RESPONSE_STATUS_SUCCESS,
            $plugin->getResponseData()
          );
      }
      catch (NotFoundHttpException $e) {
        $content = new ResponseContent(
          ResponseContent::RESPONSE_STATUS_ERROR,
          'The requested resource cannot be found on this server.',
          404
        );
      }
      catch (\Exception $e) {
        $this->logger->error("Unexpected error: {$e->getCode()} - {$e->getMessage()}");
        $content = new ResponseContent(
          ResponseContent::RESPONSE_STATUS_ERROR,
          $e->getMessage(),
          ($e->getCode() !== 0 ? $e->getCode() : 500)
        );
      }

    }
    catch (PluginNotFoundException $e) {
      $this->logger->notice("Undefined PDS-endpoint: /project/{$proj_ID}/{$type}/{$contr_ID}/{$comments}");
      $content = new ResponseContent(
        ResponseContent::RESPONSE_STATUS_ERROR,
        "Undefined PDS-endpoint: /project/{$proj_ID}/{$type}/{$contr_ID}/{$comments}",
        500
      );
    }

    if ($content->isError()) {
      $response->setData($content->getResponseContent());
      $response->setStatusCode($content->getResponseStatusCode());
    }/*
    else if ($plugin_key === 'pdscommentslist') {
      $output = [];
      foreach ($content->getResponseContent() as $feature) {
        if (is_array($feature)) {
          $output[] = $feature;
        }
      }

      $response->setData($output);
    }*/
    else {
      // Complete the GeoJSON.
      $geojson = (object) [
        'type' => 'FeatureCollection',
        'features' => [],
      ];
      foreach ($content->getResponseContent() as $feature) {
        if (is_object($feature)) {
          $geojson->features[] = $feature->getFeature();
        }
      }
      $response->setData($geojson);
    }

    $response->setEncodingOptions(static::JSON_OUTPUT_OPTIONS);

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function requestCockpitDataEndpoint($data, $parameter) {
    $response = new JsonResponse();
    $cacheId = "participation_cockpit/{$data}/{$parameter}";

    try {
      $pluginDefinition = $this->cockpitdataResponsePluginManager->getDefinition(strtolower($data));
      if (
        $pluginDefinition['isCacheable'] &&
        !$this->request->query->has('noCache') &&
        $cache = $this->cache->get($cacheId)
      ) {
        $content = $cache->data;
      }
      elseif (!in_array($this->request->getMethod(), $pluginDefinition['requestMethods'])) {
        $content = new ResponseContent(
          ResponseContent::RESPONSE_STATUS_ERROR,
          'The requested resource cannot be found on this server.',
          404
        );
      }
      else {
        /* @var \Drupal\dipas\PluginSystem\CockpitDataResponsePluginInterface $plugin */
        $plugin = new $pluginDefinition['class']($pluginDefinition, $this->request, $response);
        try {
          $content = new ResponseContent(
            ResponseContent::RESPONSE_STATUS_SUCCESS,
            $plugin->getResponseData()
          );

          $this->cache->set($cacheId, $content, \Drupal::time()->getRequestTime()  + ($pluginDefinition['maxAge'] * 60), $plugin->getCacheTags());
          if (!empty($cookies = $plugin->getCookies())) {
            foreach ($cookies as $cookie) {
              $response->headers->setCookie($cookie);
            }
          }
        }
        catch (NotFoundHttpException $e) {
          $content = new ResponseContent(
            ResponseContent::RESPONSE_STATUS_ERROR,
            'The requested resource cannot be found on this server.',
            404
          );
        }
        catch (Exception $e) {
          $this->logger->error("Unexpected error: {$e->getCode()} - {$e->getMessage()}");
          $content = new ResponseContent(
            ResponseContent::RESPONSE_STATUS_ERROR,
            $e->getMessage(),
            ($e->getCode() !== 0 ? $e->getCode() : 500)
          );
        }
      }
    }
    catch (PluginNotFoundException $e) {
      $this->logger->notice("Undefined endpoint: {$data}");
      $content = new ResponseContent(
        ResponseContent::RESPONSE_STATUS_ERROR,
        "Unknown key: {$data}",
        500
      );
    }

    $response->setData($content->getResponseContent());
    if ($content->isError()) {
      $response->setStatusCode($content->getResponseStatusCode());
    }
    $response->setEncodingOptions(static::JSON_OUTPUT_OPTIONS);
    return $response;

  }
}
