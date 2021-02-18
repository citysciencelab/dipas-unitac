<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Entity;

use Drupal\Core\Entity\EntityInterface;

/**
 * Interface MasterportalInstanceInterface.
 *
 * Defines the API of Masterportal instance configurations.
 *
 * @package Drupal\masterportal\Entity
 */
interface MasterportalInstanceInterface extends EntityInterface {

  /**
   * Returns an array of delete-protected instance configurations.
   *
   * @return array
   */
  public static function persistentInstances();

  /**
   * Holds all possible UI styles the Masterportal can be displayed in.
   *
   * @param string|false $uiStyle
   *   The desired label for a given machine ui style name.
   *
   * @return array|string
   *   The human readable label for a given style or all styles,
   *   if none is specified.
   */
  public function getUiStyleLabel($uiStyle = FALSE);

  /**
   * Returns all layer IDs, that this instance uses.
   *
   * @return array
   *   The used layer ids.
   */
  public function getAllLayerIdsInUse();

  /**
   * Returns the id of the domain if present.
   *
   * @return string|null
   *   The id of the domain or null if none is set.
   */
  public function getDomain();

  /**
   * Sets the domain for the instance.
   *
   * @param string $domain
   *   Config key of the domain.
   */
  public function setDomain($domain);

  /**
   * Returns the instance name of the instance.
   *
   * @return string|null
   */
  public function getName();

}
