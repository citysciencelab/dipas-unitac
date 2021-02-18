<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Service;

/**
 * Interface MasterportalConfigInterface
 *
 * @package Drupal\masterportal\Service
 */
interface MasterportalConfigInterface {

  /**
   * Returns the configuration values stored under a given key.
   *
   * @param string $key
   *   The key the desired configuration is stored beneath.
   *
   * @return \Drupal\Core\Config\ImmutableConfig
   *   The configuration values stored under the key given.
   */
  public function get($key);

}
