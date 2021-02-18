<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a SettingsSection annotation object.
 *
 * Plugin Namespace: Plugin\SettingsSection.
 *
 * @see \Drupal\dipas\PluginSystem\SettingsSectionPluginManager
 * @see \Drupal\dipas\PluginSystem\SettingsSectionPluginInterface
 * @see plugin_api
 *
 * @Annotation
 */
class SettingsSection extends Plugin {

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
   * The section weight.
   *
   * @var int
   */
  public $weight;

  /**
   * The system configuration that is affected by this config section.
   *
   * @var array
   */
  public $affectedConfig;

  /**
   * Permission that is required to access the settings section.
   *
   * @var string
   */
  public $permissionRequired;

}
