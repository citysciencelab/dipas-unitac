<?php

namespace Drupal\masterportal\Event;

use Drupal\domain\DomainInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class DomainDelete.
 *
 * @package Drupal\masterportal\Event
 */
class DomainDelete extends Event implements MasterportalDomainEventInterface {

  const EVENT_NAME = 'masterportal_domain_delete';

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
