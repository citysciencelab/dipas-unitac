<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\PluginSystem;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Class PluginManagerBase.
 *
 * @package Drupal\masterportal\PluginSystem
 */
abstract class PluginManagerBase extends DefaultPluginManager implements PluginManagerInterface {

  /**
   * Drupal's entity type manager service.
   *
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a PluginManagerBase object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   * @param EntityTypeManagerInterface $entity_type_manager
   *   Drupal's entity type manager service.
   */
  public function __construct(
    \Traversable $namespaces,
    CacheBackendInterface $cache_backend,
    ModuleHandlerInterface $module_handler,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    parent::__construct(
      $this->getSubdir(),
      $namespaces,
      $module_handler,
      $this->getInterface(),
      $this->getAnnotation()
    );
    $this->alterInfo($this->getPluginType());
    $this->setCacheBackend($cache_backend, $this->getPluginType());
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginDefinitions($type = FALSE) {
    $plugins = $this->getDefinitions();
    if ($type !== FALSE) {
      $plugins = array_filter(
        $plugins,
        function ($plugin) use ($type) {
          return $plugin['id'] == $type;
        }
      );
    }
    $names = [];
    foreach ($plugins as $key => $plugin) {
      $names[$key] = strtolower((string) $plugin['title']);
    }
    array_multisort($names, SORT_STRING, $plugins);
    return $type === FALSE ? $plugins : $plugins[$type];
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginTypeOptions() {
    return array_map(
      function ($plugin) {
        return $plugin['title'];
      },
      $this->getPluginDefinitions()
    );
  }

  /**
   * Returns the subdir in which these plugins are to be placed.
   *
   * @return string
   *   Returns the directory where the plugins reside in.
   */
  abstract protected function getSubdir();

  /**
   * Returns the fully qualified name of the plugin interface.
   *
   * @return string
   *   Returns the full namespace of the interface class.
   */
  abstract protected function getInterface();

  /**
   * Returns the fully qualified name of the plugin annotation.
   *
   * @return string
   *   Returns the full namespace of the annotation class.
   */
  abstract protected function getAnnotation();

}
