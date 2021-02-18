<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a SearchBarPlugin annotation object.
 *
 * Plugin Namespace: Plugin\Masterportal\SearchBar.
 *
 * @see \Drupal\masterportal\PluginSystem\SearchBarPluginPluginManager
 * @see \Drupal\masterportal\PluginSystem\SearchBarPluginInterface
 * @see plugin_api
 *
 * @Annotation
 */
class SearchBarPlugin extends Plugin {

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

}
