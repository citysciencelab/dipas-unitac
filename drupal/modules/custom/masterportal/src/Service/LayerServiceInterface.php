<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Service;

/**
 * Interface LayerServiceInterface.
 *
 * Defines the API for the layer service of the Masterportal.
 *
 * @package Drupal\masterportal\Service
 */
interface LayerServiceInterface {

  /**
   * Loads the defined layer definition file.
   *
   * @param null|string $configured_path
   *   The configured path to the static layer definition file. If not
   *   provided, the stored configuration value will be used.
   *
   * @return array
   */
  public function getStaticLayerDefinitions($configured_path = NULL);

  /**
   * Collects all available layer plugins and returns their layer definitions.
   *
   * @return array
   */
  public function getPluginLayerDefinitions();

  /**
   * Returns the custom defined layers.
   *
   * @return array
   */
  public function getCustomLayerDefinitions();

  /**
   * Returns the definition of a single layer.
   *
   * @param mixed $layerid
   *   The ID of the layer in question.
   *
   * @return \stdClass|string
   *   The layer definition (if existent, false if otherwise).
   */
  public function getLayerDefinition($layerid);

  /**
   * Returns ready-to-use options of existing layer definitions.
   *
   * @return array
   *   A key-value type array containing existing layers.
   */
  public function getLayerOptions();

  /**
   * Returns all layer definitions.
   *
   * @return array
   *   The layer definitions.
   */
  public function getDefinitions();

  /**
   * Returns a layer name for composite IDs.
   *
   * @param array $ids
   *   The layer IDs to retrieve the labels for.
   *
   * @return string
   *   Calculated layer name for the given IDs.
   */
  public function getLayerNameForCompositeIds(array $ids);

  /**
   * Checks the configured layer definitions for any changes.
   *
   * @param string $configured_path
   *   The configured path of the definition file.
   *
   * @return TRUE|string
   *   TRUE, if the definitions are valid, a string containing the error message if not.
   */
  public function checkLayerDefinitions($configured_path);

  /**
   * Re-validate the layer definitions for any changes and invalidate cache tags.
   *
   * @param string $configured_path
   *   The path for the static layer definition file. If not provided,
   *   the stored configuration value will be used.
   *
   * @return void
   */
  public function checkForLayerChanges($configured_path = NULL);

}
