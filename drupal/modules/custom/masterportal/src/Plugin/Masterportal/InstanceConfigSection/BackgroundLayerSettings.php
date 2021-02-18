<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Plugin\Masterportal\InstanceConfigSection;

/**
 * Defines a BackgroundLayerSettings configuration section.
 *
 * @InstanceConfigSection(
 *   id = "BackgroundLayerSettings",
 *   title = @Translation("Background layer"),
 *   description = @Translation("The background layer configuration for the Masterportal instance."),
 *   sectionWeight = 2
 * )
 */
class BackgroundLayerSettings extends LayerSectionBase {

  /**
   * {@inheritdoc}
   */
  public static function getSectionProperty() {
    return 'BackgroundLayerSettings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getSectionConfigName() {
    return 'Hintergrundkarten';
  }

}
