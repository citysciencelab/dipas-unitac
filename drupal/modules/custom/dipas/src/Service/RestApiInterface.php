<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Service;

interface RestApiInterface {

  /**
   * Generates a JSONd output of the requested key.
   *
   * @param string $key
   *   The key of the desired configuration.
   *
   * @return string
   *   The JSONd contents of the key requested.
   */
  public function requestEndpoint($key);

  /**
   * Generates a JSONd output of the requested project or/and contribution.
   *  Follows the PDS standard.
   *
   * @param string $proj_ID
   *   The project id of the desired project.
   *
   * @param string $type
   *   The type of data to be requested ('none', 'contributions' or 'conceptions').
   *
   * @param integer $contr_ID
   *   The node id of the desired contribution.
   *
   * @param string $comments
   *   Indicator, if only comments to the requested type shall be returned ('none', 'comments').
   *
   *
   * @return string
   *   The JSONd contents of the endpoint requested.
   */
  public function requestPDSEndpoint($proj_ID, $type, $contr_ID, $comments);

   /**
   * Generates a JSONd output of the requested dataType for the participation cockpit.
   *
   * @param string $dataType
   *   The data type of the required data.
   *
   * @return string
   *   The JSONd contents of the key requested.
   */
  public function requestCockpitDataEndpoint($dataType);

}
