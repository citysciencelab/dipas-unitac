<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a LayerPlugin annotation object.
 *
 * Plugin Namespace: Plugin\Masterportal\Layer.
 *
 * @see \Drupal\masterportal\PluginSystem\LayerPluginManager
 * @see \Drupal\masterportal\PluginSystem\LayerPluginInterface
 * @see plugin_api
 *
 * @Annotation
 */
class Layer extends Plugin {

  /**
   * The plugin id as well as the layer ID.
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
