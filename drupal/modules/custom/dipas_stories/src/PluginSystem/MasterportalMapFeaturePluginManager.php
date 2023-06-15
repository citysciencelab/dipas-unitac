<?php

namespace Drupal\dipas_stories\PluginSystem;

use Drupal\masterportal\PluginSystem\PluginManagerBase;

class MasterportalMapFeaturePluginManager extends PluginManagerBase {

  const PLUGIN_TYPE = 'masterportal_map_feature_plugin';

  /**
   * {@inheritdoc}
   */
  protected function getSubdir() {
    return 'Plugin/Masterportal/MapFeature';
  }

  /**
   * {@inheritdoc}
   */
  protected function getInterface() {
    return 'Drupal\dipas_stories\PluginSystem\MasterportalSettingsSectionPluginInterface';
  }

  /**
   * {@inheritdoc}
   */
  protected function getAnnotation() {
    return 'Drupal\dipas_stories\Annotation\MasterportalMapFeature';
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginType() {
    return self::PLUGIN_TYPE;
  }

}
