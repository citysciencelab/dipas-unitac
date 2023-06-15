<?php

namespace Drupal\dipas_stories\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a MasterportalMapFeature annotation object.
 *
 * Plugin Namespace: Plugin\Masterportal\MapFeature.
 *
 * @see \Drupal\dipas_stories\PluginSystem\MasterportalMapFeaturePluginManager
 * @see \Drupal\dipas_stories\PluginSystem\MasterportalMapFeaturePluginInterface
 * @see plugin_api
 *
 * @Annotation
 */
class MasterportalMapFeature extends Plugin {

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
   * Should this plugin get integrated in map configuration preview mode?
   *
   * @var bool
   */
  public $integrateInPreviewMode = FALSE;

  /**
   * Possible libraries to attach to the field widget.
   *
   * @var array
   */
  public $libraries = [];

}
