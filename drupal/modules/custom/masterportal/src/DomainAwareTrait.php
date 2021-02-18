<?php

namespace Drupal\masterportal;

use Drupal\Core\Entity\Query\QueryInterface;

/**
 * Trait DomainAwareTrait.
 *
 * @package Drupal\masterportal
 */
trait DomainAwareTrait {

  /**
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * @var \Drupal\domain\DomainNegotiatorInterface
   */
  protected $domainNegotiator;

  /**
   * @return string
   */
  protected function getActiveDomain() {
    if ($this->isDomainModuleInstalled()) {
      $domainNegotiator = $this->getDomainNegotiator();

      /*
       * In prevent null pointer exception because the domain isn't set.
       * Happens in drush. Just fallback to `default`.
       */
      $domain = $domainNegotiator->getActiveDomain();
      if ($domain) {
        return $domain->id();
      }
    }
    return 'default';
  }

  /**
   * @return bool
   */
  protected function isDefaultDomain() {
    if ($this->isDomainModuleInstalled()) {
      $domainNegotiator = $this->getDomainNegotiator();
      return $domainNegotiator->getActiveDomain()->isDefault();
    }
    return TRUE;
  }

  /**
   * @return bool
   */
  protected function isDomainModuleInstalled() {
    return $this->getModuleHandler()->moduleExists('domain');
  }

  /**
   * @param \Drupal\Core\Entity\Query\QueryInterface $query
   *
   * @return void
   */
  protected function makeEntityQueryDomainSensitive(QueryInterface &$query) {
    $domainCondition = $query->orConditionGroup();
    $domainCondition->condition('field_domain_access', $this->getActiveDomain(), '=');
    $domainCondition->condition('field_domain_all_affiliates', '1', '=');
    $query->condition($domainCondition);
  }

  /**
   * @return \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected function getModuleHandler() {
    if (!isset($this->moduleHandler)) {
      $this->moduleHandler = \Drupal::service('module_handler');
    }
    return $this->moduleHandler;
  }

  /**
   * @return \Drupal\domain\DomainNegotiatorInterface|null
   */
  protected function getDomainNegotiator() {
    if ($this->isDomainModuleInstalled()) {
      if (!isset($this->domainNegotiator)) {
        $this->domainNegotiator = \Drupal::service('domain.negotiator');
      }
      return $this->domainNegotiator;
    }

    return NULL;
  }

  /**
   *  Check if the requested domain is configured in dipas system
   *
   * @return bool
   *   TRUE if a URL's hostname is registered as a valid domain or alias, or
   *   FALSE.
   */
  protected function isDomainDefined() {
    if ($this->isDomainModuleInstalled()) {
      $domainNegotiator = $this->getDomainNegotiator();
      $domain = $domainNegotiator->getActiveDomain();
      if ($domain && $domain->getMatchType() && $domain->status() && !$domain->isDefault()) {
        return TRUE;
      }
      return FALSE;
    }
    return TRUE;
  }

}
