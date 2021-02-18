<?php

namespace Drupal\domain_dipas\Entity;

use Drupal\masterportal\DomainAwareTrait;
use Drupal\taxonomy\TermInterface;
use Drupal\taxonomy\TermStorage;

/**
 * Class TaxonomyTermStorage.
 *
 * Extends the default {@link TaxonomyStorage} to filter the Terms by assigned
 * domains.
 *
 * @package Drupal\domain_dipas\Entity
 */
class TaxonomyTermStorage extends TermStorage {

  use DomainAwareTrait;

  /**
   * {@inheritDoc}
   */
  public function loadTree($vid, $parent = 0, $max_depth = NULL, $load_entities = FALSE) {
    $tree = parent::loadTree($vid, $parent, $max_depth, $load_entities);

    $activeDomainId = $this->getActiveDomain();

    $filteredTree = array_filter($tree, static function ($term) use ($activeDomainId) {

      /*
       * In case $load_entities is false the Terms are loaded as plain instances
       * of stdClass without the fields we need. This happens if they are loaded
       * for the "Parent terms" field in the relations. We leave all Terms in to
       * prevent further errors because of missing tree elements.
       */
      if ($term instanceof TermInterface) {
        $accessAllField = $term->get(DOMAIN_ACCESS_ALL_FIELD);

        if (($first = $accessAllField->first()) && (bool) $first->getValue()['value']) {
          // The term is marked as "available for all domains".
          return TRUE;
        }

        $accessField = $term->get(DOMAIN_ACCESS_FIELD);

        foreach ($accessField->getValue() as $domainId) {
          if ($domainId['target_id'] === $activeDomainId) {
            // The term is marked as "available for this domain".
            return TRUE;
          }
        }

        // No marking for this domain found.
        return FALSE;
      }
      return TRUE;
    });

    return array_values($filteredTree);
  }

}
