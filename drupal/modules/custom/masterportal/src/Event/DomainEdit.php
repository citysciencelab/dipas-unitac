<?php

namespace Drupal\masterportal\Event;

use Drupal\domain\DomainInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class DomainEdit.
 *
 * @package Drupal\masterportal\Event
 */
class DomainEdit extends Event implements MasterportalDomainEventInterface {

  const EVENT_NAME = 'masterportal_domain_edit';

  /**
   * @var \Drupal\domain\DomainInterface
   */
  protected $domain;

  /**
   * DomainCreate constructor.
   *
   * @param \Drupal\domain\DomainInterface $domain
   *   The domain of the event.
   */
  public function __construct(DomainInterface $domain) {
    $this->domain = $domain;
  }

  /**
   * {@inheritdoc}
   */
  public function getDomain() {
    return $this->domain;
  }

}
