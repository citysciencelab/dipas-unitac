<?php


namespace Drupal\dipas\Plugin\SettingsSection;

use Drupal\masterportal\DomainAwareTrait;

/**
 * Trait NodeSelectionTrait.
 *
 * The trait provides a list of all nodes available on the current domain, keyed
 * by the node id.
 *
 * @package Drupal\dipas\Plugin\SettingsSection
 */
trait NodeSelectionTrait {

  use DomainAwareTrait;

  /**
   * Returns an array of page titles keyed by node id.
   *
   * The list includes all 'page' nodes available on the current domain, keyed
   * by the node id. The first item of the list is a "Please select" entry.
   *
   * @return array
   */
  protected function getPageOptions() {
    $pageOptions = drupal_static('dipas_page_options', NULL);

    if (is_null($pageOptions)) {
      // Fetch all pages for this proceeding.
      $query = $this->nodeStorage->getQuery();
      $query->condition('type', 'page', '=');
      $query->condition('status', 1, '=');
      if ($this->isDomainModuleInstalled()) {
        $domainConditions = $query->orConditionGroup();
        $domainConditions->condition('field_domain_access', $this->getActiveDomain(), '=');
        $domainConditions->condition('field_domain_all_affiliates', '1', '=');
        $query->condition($domainConditions);
      }
      $pageIDs = $query->execute();
      $pageOptions = [
        '' => $this->t('Please choose'),
      ];

      foreach ($this->nodeStorage->loadMultiple($pageIDs) as $node) {
        $pageOptions[$node->id()] = sprintf('%s (Node-ID %d)', $node->label(), $node->id());
      }
    }

    return $pageOptions;
  }

}
