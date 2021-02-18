<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\PluginSystem;

/**
 * Provides a plugin manager for LayerStyle plugins.
 *
 * @see plugin_api
 */
class LayerStylePluginManager extends PluginManagerBase {

  const PLUGIN_TYPE = 'layer_style';

  /**
   * {@inheritdoc}
   */
  protected function getSubdir() {
    return 'Plugin/Masterportal/LayerStyle';
  }

  /**
   * {@inheritdoc}
   */
  protected function getInterface() {
    return 'Drupal\masterportal\PluginSystem\LayerStylePluginInterface';
  }

  /**
   * {@inheritdoc}
   */
  protected function getAnnotation() {
    return 'Drupal\masterportal\Annotation\LayerStyle';
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginType() {
    return self::PLUGIN_TYPE;
  }

}
