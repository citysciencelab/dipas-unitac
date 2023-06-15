<?php

namespace Drupal\dipas_stories\Plugin\Field\FieldWidget\MasterportalSettingsSection;

use Drupal\dipas_stories\Annotation\MasterportalSettingsSection;

/**
 * Defines a MasterportalSettingsSection plugin implementation for background map layer settings.
 *
 * @MasterportalSettingsSection(
 *   id = "BackgroundLayer",
 *   title = @Translation("Background layers"),
 *   description = @Translation("Choose and configure background layers for the DIPAS story Masterportal"),
 *   weight = 2
 * )
 */
class BackgroundLayerSettings extends LayerSettingsBase {

  /**
   * {@inheritdoc}
   */
  protected function getLayerPropertyName() {
    return 'Background';
  }

}
