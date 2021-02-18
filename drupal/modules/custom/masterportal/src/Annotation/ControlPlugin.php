<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a ControlPlugin annotation object.
 *
 * Plugin Namespace: Plugin\Masterportal\Controls.
 *
 * @see \Drupal\masterportal\PluginSystem\ControlPluginManager
 * @see \Drupal\masterportal\PluginSystem\ControlPluginInterface
 * @see plugin_api
 *
 * @Annotation
 */
class ControlPlugin extends Plugin {

  /**
   * The plugin id.
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

  /**
   * The description of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description;

  /**
   * The category of the plugin.
   *
   * Either "button", "display" or "utility". Used for sorting only.
   *
   * @var string
   */
  public $category;

  /**
   * The configuration property string.
   *
   * @var string
   */
  public $configProperty;

}
