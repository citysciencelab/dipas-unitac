<?php

namespace Drupal\masterportal\Event;

use stdClass;

interface MasterportalCacheEventsInterface {

  /**
   * Returns the Masterportal instance of the event.
   *
   * @return \Drupal\masterportal\Entity\MasterportalInstanceInterface|FALSE
   */
  public function getMasterportalInstance();

  /**
   * Returns the current configuration settings.
   *
   * @return String|array
   */
  public function getConfiguration();

  /**
   * Sets the configuration settings.
   *
   * @param String|array $configuration
   *
   * @return void
   */
  public function setConfiguration(String|array $configuration);

}
