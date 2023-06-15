<?php

namespace Drupal\dipas_stories\Plugin\Field\FieldWidget\MasterportalSettingsSection;

use Drupal\dipas_stories\Annotation\MasterportalSettingsSection;

/**
 * Defines a MasterportalSettingsSection plugin implementation for foreground map layer settings.
 *
 * @MasterportalSettingsSection(
 *   id = "ForegroundLayer",
 *   title = @Translation("Foreground layers"),
 *   description = @Translation("Choose and configure foreground layers for the DIPAS story Masterportal"),
 *   weight = 1
 * )
 */
class ForegroundLayerSettings extends LayerSettingsBase {

  /**
   * {@inheritdoc}
   */
  protected function getLayerPropertyName() {
    return 'Foreground';
  }

}
