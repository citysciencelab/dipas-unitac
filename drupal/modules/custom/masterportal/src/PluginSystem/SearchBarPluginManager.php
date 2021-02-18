<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\PluginSystem;

/**
 * Provides a plugin manager for SearchBarPlugin plugins.
 *
 * @see plugin_api
 */
class SearchBarPluginManager extends PluginManagerBase {

  const PLUGIN_TYPE = 'searchbar_plugin';

  /**
   * {@inheritdoc}
   */
  protected function getSubdir() {
    return 'Plugin/Masterportal/SearchBar';
  }

  /**
   * {@inheritdoc}
   */
  protected function getInterface() {
    return 'Drupal\masterportal\PluginSystem\SearchBarPluginInterface';
  }

  /**
   * {@inheritdoc}
   */
  protected function getAnnotation() {
    return 'Drupal\masterportal\Annotation\SearchBarPlugin';
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginType() {
    return self::PLUGIN_TYPE;
  }

}
