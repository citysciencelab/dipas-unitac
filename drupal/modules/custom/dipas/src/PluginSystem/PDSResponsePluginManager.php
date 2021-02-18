<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\PluginSystem;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Class PDSResponsePluginManager.
 *
 * @package Drupal\dipas\PluginSystem
 */
class PDSResponsePluginManager extends DefaultPluginManager implements PDSResponsePluginManagerInterface {

  /**
   * Constructs a PDSResponsePluginManager object.
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
      'Plugin/PDSResponse',
      $namespaces,
      $module_handler,
      'Drupal\dipas\PluginSystem\PDSResponsePluginManagerInterface',
      'Drupal\dipas\Annotation\PDSResponse'
    );
    $this->alterInfo('pds_response');
    $this->setCacheBackend($cache_backend, 'pds_response');
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginDefinition($key) {
    $plugins = $this->getDefinitions();
    return !empty($plugins[$key]) ? $plugins[$key] : FALSE;
  }

}
