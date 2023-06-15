<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\PluginSystem;

/**
 * Interface ResponseKeyPluginInterface.
 *
 * Defined the API for response key plugins.
 *
 * @package Drupal\dipas\PluginSystem
 */
interface ResponseKeyPluginInterface {

  /**
   * Returns the response data array.
   *
   * @return array
   *   The array containing the data.
   */
  public function getResponseData();

  /**
   * Processes plugin response data before delivery (even cached data).
   *
   * @param array $responsedata
   *   The (potentionally cached) plugin response.
   *
   * @return array
   *   The processed plugin response data
   */
  public static function postProcessResponse(array $responsedata);

  /**
   * Returns a list of applicable cache tags for the response data.
   *
   * @return string[]
   */
  public function getCacheTags();

  /**
   * Returns cookies to set on the response.
   *
   * @return \Symfony\Component\HttpFoundation\Cookie[]
   */
  public function getCookies();

}
