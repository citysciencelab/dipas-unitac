<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Plugin\Masterportal\InstanceConfigSection;

/**
 * Interface LayerSectionPluginInterface.
 *
 * Defines the API of configuration sections for layers.
 *
 * @package Drupal\masterportal\Plugin\Masterportal\InstanceConfigSection
 */
interface LayerSectionPluginInterface {

  /**
   * Return the layer IDs in use by the actual section.
   *
   * @return array
   *   The used layer ids.
   */
  public function getLayerIdsInUse();

}
