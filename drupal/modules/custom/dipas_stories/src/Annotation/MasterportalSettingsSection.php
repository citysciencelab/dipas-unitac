<?php

namespace Drupal\dipas_stories\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a MasterportalSettingsSection annotation object.
 *
 * Plugin Namespace: Plugin\Field\FieldWidget\MasterportalSettingsSection.
 *
 * @see \Drupal\dipas_stories\PluginSystem\MasterportalSettingsSectionPluginManager
 * @see \Drupal\dipas_stories\PluginSystem\MasterportalSettingsSectionPluginInterface
 * @see plugin_api
 *
 * @Annotation
 */
class MasterportalSettingsSection extends Plugin {

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
   * The weight of the config section.
   *
   * @var int
   */
  public $weight;

}
