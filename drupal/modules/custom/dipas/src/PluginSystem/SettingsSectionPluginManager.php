<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\PluginSystem;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Class SettingsSectionPluginManager.
 *
 * @package Drupal\dipas\PluginSystem
 */
class SettingsSectionPluginManager extends DefaultPluginManager implements SettingsSectionPluginManagerInterface {

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
      'Plugin/SettingsSection',
      $namespaces,
      $module_handler,
      'Drupal\dipas\PluginSystem\SettingsSectionPluginInterface',
      'Drupal\dipas\Annotation\SettingsSection'
    );
    $this->alterInfo('settings_section');
    $this->setCacheBackend($cache_backend, 'settings_section');
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginDefinitions($pluginId = FALSE) {
    $plugins = $this->getDefinitions();
    if ($pluginId === FALSE) {
      $names = [];
      $weights = [];
      foreach ($plugins as $key => $plugin) {
        $names[$key] = strtolower((string) $plugin['title']);
        $weights[$key] = (int) $plugin['weight'];
      }
      array_multisort($weights, SORT_ASC, $names, SORT_STRING, $plugins);
    }
    return $pluginId !== FALSE ? $plugins[$pluginId] : $plugins;
  }

}
