<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\PluginSystem;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Class ResponseKeyPluginManager.
 *
 * @package Drupal\dipas\PluginSystem
 */
class ResponseKeyPluginManager extends DefaultPluginManager implements ResponseKeyPluginManagerInterface {

  /**
   * Constructs a ResponseKeyPluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(
    \Traversable $namespaces,
    CacheBackendInterface $cache_backend,
    ModuleHandlerInterface $module_handler
  ) {
    parent::__construct(
      'Plugin/ResponseKey',
      $namespaces,
      $module_handler,
      'Drupal\dipas\PluginSystem\ResponseKeyPluginInterface',
      'Drupal\dipas\Annotation\ResponseKey'
    );
    $this->alterInfo('response_key');
    $this->setCacheBackend($cache_backend, 'response_key');
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginDefinition($key) {
    $plugins = $this->getDefinitions();
    return !empty($plugins[$key]) ? $plugins[$key] : FALSE;
  }

}
