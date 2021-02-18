<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\PluginSystem;

/**
 * Interface LayerStylePluginInterface.
 *
 * Describes the API for layer style plugins.
 *
 * @package Drupal\masterportal\PluginSystem
 */
interface LayerStylePluginInterface {

  /**
   * Returns the style definition in a structured object ready to be serialized.
   *
   * @return \stdClass
   *   The structured style settings.
   */
  public function getStyleObject();

  /**
   * Returns cache tags associated with the layer style data.
   *
   * @return string[]
   */
  public function getCacheTags();

}
