<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\PluginSystem;

/**
 * Interface PDSResponsePluginInterface.
 *
 * Defined the API for PDS response plugins.
 *
 * @package Drupal\dipas\PluginSystem
 */
interface PDSResponsePluginInterface {

  /**
   * Returns the response data array.
   *
   * @return array
   *   The array containing the data.
   */
  public function getResponseData();

  /**
   * Returns a list of applicable cache tags for the response data.
   *
   * @return string[]
   */
  public function getCacheTags();

}
