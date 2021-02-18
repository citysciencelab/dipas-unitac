<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\PluginSystem;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Class SidebarBlockPluginManager.
 *
 * @package Drupal\dipas\PluginSystem
 */
class SidebarBlockPluginManager extends DefaultPluginManager implements SidebarBlockPluginManagerInterface {

  /**
   * Constructs a SettingsSectionPluginManager object.
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
      'Plugin/SidebarBlock',
      $namespaces,
      $module_handler,
      'Drupal\dipas\PluginSystem\SidebarBlockPluginInterface',
      'Drupal\dipas\Annotation\SidebarBlock'
    );
    $this->alterInfo('sidebar_block');
    $this->setCacheBackend($cache_backend, 'sidebar_block');
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginDefinitions($pluginId = FALSE) {
    $plugins = $this->getDefinitions();
    return $pluginId !== FALSE ? $plugins[$pluginId] : $plugins;
  }

}
