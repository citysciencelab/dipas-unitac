<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Service;

/**
 * Interface InstanceServiceInterface.
 *
 * Defines the API for the instance service of the Masterportal.
 *
 * @package Drupal\masterportal\Service
 */
interface InstanceServiceInterface {

  /**
   * Generates ready-to-use options for a form API select with instances.
   * Only returns Instances belonging to the domain.
   *
   * @param array $hideInstances
   *   Instances that should get hidden from the options.
   *
   * @return array
   *   The configured instances.
   */
  public function getInstanceOptions(array $hideInstances = []);

  /**
   * Loads and returns a Masterportal instance.
   *
   * @param string $id
   *   The ID of the instance to load.
   *
   * @return \Drupal\masterportal\Entity\MasterportalInstanceInterface|false
   *   The instance or FALSE, if something fails.
   */
  public function loadInstance($id);

  /**
   * Checks configured instances for removed layers.
   *
   * @param string[] $removed_layer_ids
   *   An array of layer ids that have been removed.
   *
   * @return void
   */
  public function checkInstancesForRemovedLayers(array $removed_layer_ids);

}
