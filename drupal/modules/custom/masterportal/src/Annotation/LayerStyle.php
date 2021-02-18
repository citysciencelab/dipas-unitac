<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a LayerStylePlugin annotation object.
 *
 * Plugin Namespace: Plugin\Masterportal\LayerStyle.
 *
 * @see \Drupal\masterportal\PluginSystem\LayerStylePluginManager
 * @see \Drupal\masterportal\PluginSystem\LayerStylePluginInterface
 * @see plugin_api
 *
 * @Annotation
 */
class LayerStyle extends Plugin {

  /**
   * The plugin id as well as the layer ID this style is for.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $title;

}
