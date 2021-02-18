<?php

namespace Drupal\masterportal\Event;

/**
 * Interface MasterportalDomainEventInterface
 *
 * @package Drupal\masterportal\Event
 */
interface MasterportalDomainEventInterface {

  /**
   * Returns the domain entity of the event.
   *
   * @return \Drupal\domain\DomainInterface
   */
  public function getDomain();

}
