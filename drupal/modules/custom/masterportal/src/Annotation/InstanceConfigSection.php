<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a InstanceConfigSection annotation object.
 *
 * Plugin Namespace: Plugin\Masterportal\InstanceConfigSection.
 *
 * @see \Drupal\masterportal\PluginSystem\InstanceConfigSectionManager
 * @see \Drupal\masterportal\PluginSystem\InstanceConfigSectionInterface
 * @see plugin_api
 *
 * @Annotation
 */
class InstanceConfigSection extends Plugin {

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
   * The weight of the form section.
   *
   * @var int
   */
  public $sectionWeight;

}
