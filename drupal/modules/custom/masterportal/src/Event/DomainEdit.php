<?php

namespace Drupal\masterportal\Event;

use Drupal\domain\DomainInterface;
use Symfony\Contracts\EventDispatcher\Event;

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
   * @var string
   */
  protected $domain_previous_id;

  /**
   * DomainEdit constructor.
   *
   * @param \Drupal\domain\DomainInterface $domain
   *   The domain of the event.
   * @param string $previousId
   *   the previous domain-id of the domain
   */
  public function __construct(DomainInterface $domain, string $previousId) {
    $this->domain = $domain;
    $this->domain_previous_id = $previousId;
  }

  /**
   * {@inheritdoc}
   */
  public function getDomain() {
    return $this->domain;
  }

  /**
   * @return string previousId
   */
  public function getPreviousDomain() {
    return $this->domain_previous_id;
  }

}
