<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a ToolPlugin annotation object.
 *
 * Plugin Namespace: Plugin\Masterportal\Tools.
 *
 * @see \Drupal\masterportal\PluginSystem\ToolPluginManager
 * @see \Drupal\masterportal\PluginSystem\ToolPluginInterface
 * @see plugin_api
 *
 * @Annotation
 */
class ToolPlugin extends Plugin {

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
   * The configuration property string.
   *
   * @var string
   */
  public $configProperty;

  /**
   * Indicated whether the tool must be loaded as addon
   *
   * @var boolean
   */
  public $isAddon = false;

}
