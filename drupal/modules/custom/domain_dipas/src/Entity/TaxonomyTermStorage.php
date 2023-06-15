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

    // Check if the tree has a fields property
    // If there is no fields property it will render a non filtered tree (taxonomies that are
    // not assigned to any domain)
    if (
      isset($tree[0]) &&
      !empty($tree[0]) &&
      method_exists($tree[0], 'getFieldDefinition') &&
      $tree[0]->getFieldDefinition(\Drupal\domain_access\DomainAccessManagerInterface::DOMAIN_ACCESS_FIELD)
    ) {
      $filteredTree = array_filter($tree, static function ($term) use ($tree, $activeDomainId) {
        /*
         * In case $load_entities is false the Terms are loaded as plain instances
         * of stdClass without the fields we need. This happens if they are loaded
         * for the "Parent terms" field in the relations. We leave all Terms in to
         * prevent further errors because of missing tree elements.
         */
        if ($term instanceof TermInterface) {
          $accessAllField = $term->get(\Drupal\domain_access\DomainAccessManagerInterface::DOMAIN_ACCESS_ALL_FIELD);

          if (($first = $accessAllField->first()) && (bool) $first->getValue()['value']) {
            // The term is marked as "available for all domains".
            return TRUE;
          }

          $accessField = $term->get(\Drupal\domain_access\DomainAccessManagerInterface::DOMAIN_ACCESS_FIELD);

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
    } else {
      $filteredTree = $tree;
    }



    return array_values($filteredTree);
  }

}
