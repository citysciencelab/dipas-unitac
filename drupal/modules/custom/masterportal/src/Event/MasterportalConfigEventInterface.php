<?php

namespace Drupal\masterportal\Event;

use stdClass;

interface MasterportalConfigEventInterface {

  /**
   * Returns the Masterportal instance of the event.
   *
   * @return \Drupal\masterportal\Entity\MasterportalInstanceInterface|FALSE
   */
  public function getMasterportalInstance();

  /**
   * Returns the current configuration settings.
   *
   * @return stdClass|array
   */
  public function getConfiguration();

  /**
   * Sets the configuration settings.
   *
   * @param stdClass|array $configuration
   *
   * @return void
   */
  public function setConfiguration(stdClass|array $configuration);

  /**
   * Sets cache tags to be used with the current request.
   *
   * @param array $cacheTags
   *
   * @return void
   */
  public function setCacheTags(array $cacheTags);

  /**
   * Returns potential cache tags set by the event listener.
   *
   * @return array
   */
  public function getCacheTags();

}
