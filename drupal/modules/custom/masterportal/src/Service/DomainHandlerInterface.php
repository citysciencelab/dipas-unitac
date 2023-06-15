<?php

namespace Drupal\masterportal\Service;

use Drupal\domain\DomainInterface;

/**
 * Interface DomainHandlerInterface
 *
 * @package Drupal\masterportal\Service
 */
interface DomainHandlerInterface {

  /**
   * Gets called when a new domain entry is created or an existing one is changed.
   *
   * @param \Drupal\domain\DomainInterface $domain
   * @param bool $isNew
   * @param string $previousId
   */
  public function onDomainEdit(DomainInterface $domain, bool $isNew, string $previousId);

  /**
   * Gets called when a domain entry gets deleted.
   *
   * @param \Drupal\domain\DomainInterface $domain
   */
  public function onDomainDelete(DomainInterface $domain);

}
