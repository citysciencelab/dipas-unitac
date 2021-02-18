<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\PluginSystem;

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface LayerPluginInterface.
 *
 * Describes the API for layer plugins.
 *
 * @package Drupal\masterportal\PluginSystem
 */
interface LayerPluginInterface {

  /**
   * LayerPluginInterface constructor.
   *
   * @param \Symfony\Component\HttpFoundation\Request $current_request
   */
  public function __construct(Request $current_request);

  /**
   * Returns the layer definition of the GeoJSON layer provided.
   *
   * @return \stdClass
   *   The layer definition object.
   */
  public function getLayerDefinition();

  /**
   * Returns the actual GeoJSON data of the layer.
   *
   * @return \Drupal\masterportal\GeoJSONFeatureInterface[]
   */
  public function getGeoJSONFeatures();

  /**
   * Returns cache tags associated with the layer data.
   *
   * @return array
   */
  public function getCacheTags();

}
