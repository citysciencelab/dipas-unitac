<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Plugin\Masterportal\InstanceConfigSection;

/**
 * Defines a ForegroundLayerSection configuration section.
 *
 * @InstanceConfigSection(
 *   id = "ForegroundLayerSection",
 *   title = @Translation("Foreground layer"),
 *   description = @Translation("The data layer configuration for the Masterportal instance."),
 *   sectionWeight = 1
 * )
 */
class ForegroundLayerSection extends LayerSectionBase {

  /**
   * {@inheritdoc}
   */
  public static function getSectionProperty() {
    return 'ForegroundLayerSection';
  }

  /**
   * {@inheritdoc}
   */
  protected function getSectionConfigName() {
    return 'Fachdaten';
  }

}
