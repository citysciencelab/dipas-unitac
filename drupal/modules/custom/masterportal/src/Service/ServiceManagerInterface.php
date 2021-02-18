<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Service;

/**
 * Interface ServiceManagerInterface.
 *
 * Defines the API for the service manager of the Masterportal.
 *
 * @package Drupal\masterportal\Service
 */
interface ServiceManagerInterface {

  /**
   * Returns ready-to-use form API options containing the configured services.
   *
   * @return array
   *   The form API compatible options array.
   */
  public function getServiceOptions();

}
