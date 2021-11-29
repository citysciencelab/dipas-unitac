<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\PluginSystem;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Class CockpitDataResponsePluginManager.
 *
 * @package Drupal\dipas\PluginSystem
 */
class CockpitDataResponsePluginManager extends DefaultPluginManager implements CockpitDataResponsePluginManagerInterface {

  /**
   * Constructs a CockpitDataResponsePluginManager object.
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
      'Plugin/CockpitDataResponse',
      $namespaces,
      $module_handler,
      'Drupal\dipas\PluginSystem\CockpitDataResponsePluginManagerInterface',
      'Drupal\dipas\Annotation\CockpitDataResponse'
    );
    $this->alterInfo('cockpitData_response');
    $this->setCacheBackend($cache_backend, 'cockpitData_response');
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginDefinition($key) {
    $plugins = $this->getDefinitions();
    return !empty($plugins[$key]) ? $plugins[$key] : FALSE;
  }

}
