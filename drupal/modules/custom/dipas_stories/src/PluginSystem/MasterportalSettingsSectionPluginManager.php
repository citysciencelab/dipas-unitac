<?php

namespace Drupal\dipas_stories\PluginSystem;

use Drupal\masterportal\PluginSystem\PluginManagerBase;

class MasterportalSettingsSectionPluginManager extends PluginManagerBase {

  const PLUGIN_TYPE = 'masterportal_settings_section_plugin';

  /**
   * {@inheritdoc}
   */
  protected function getSubdir() {
    return 'Plugin/Field/FieldWidget/MasterportalSettingsSection';
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
    return 'Drupal\dipas_stories\Annotation\MasterportalSettingsSection';
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginType() {
    return self::PLUGIN_TYPE;
  }

}
