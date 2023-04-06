<?php

namespace Drupal\dipas;

/**
 * Trait TaxonomyTermFunctionsTrait.
 *
 * @package Drupal\dipas
 */
trait TaxonomyTermFunctionsTrait {

  /**
   * Helper function to retrieve a list of taxonomy terms from a given vocabulary.
   *
   * @param String $vocab
   *   The vocabulary ID
   * @param Array $include_fields
   *   An array of fields to include from the terms fetched in the form ['fieldname' => function () { return $value; }]
   * @param Boolean $allDomains
   *   Boolean indicating if the list should get filtered by domain affiliation
   * @param String $domain_id
   *   The name/id of the project
   *
   * @return Array
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function getTermList($vocab, array $include_fields = [], $allDomains = FALSE, $domain_id = null) {
    $termlist = drupal_static('dipas_termlist', []);

    $fields = array_keys($include_fields);
    sort($fields);

    $domainSelector = $allDomains ? 'all' : $this->getActiveDomain();
    $fieldSelector = count($fields) ? implode(",", $fields) : '--nofields--';

    if (!isset($termlist[$vocab][$domainSelector][$fieldSelector])) {
      $terms = $this->fetchTerms($vocab);

      if (
        count($terms) &&
        $this->isDomainModuleInstalled() &&
        !$allDomains
      ) {
        $terms = $this->filterTerms($terms, !is_null($domain_id) ? $domain_id : $this->getActiveDomain());
      }

      $list = $this->sortAndSimplifyTerms($terms);

      foreach ($include_fields as $field => $preprocess) {
        array_walk($list, function (&$termdata, $tid) use ($terms, $field, $preprocess) {
          $termdata[$field] = $preprocess($terms[$tid]->get($field)->first());
        });
      }

      $termlist[$vocab][$domainSelector][$fieldSelector] = $list;
    }

    return $termlist[$vocab][$domainSelector][$fieldSelector];
  }

  /**
   * Fetchs all terms with given vocabulary
   * @param String $vocab
   * The vocabulary ID
   * @return Array terms from database.
   */
  protected function fetchTerms($vocab) {
    return $this->getTermStorage()->loadByProperties(['vid' => $vocab]);
  }

  /**
   * Filters Terms according to domain_id
   *
   * @param Array $terms
   *  The terms that is going to be filtered.
   * @param String $domain_id
   *  The name/od of the project. The parameter to filter array.
   * @return Array $terms
   */
  protected function filterTerms($terms, $domain_id) {
    $hasDomainAccessField = reset($terms)->hasField(\Drupal\domain_access\DomainAccessManagerInterface::DOMAIN_ACCESS_FIELD);
    $hasDomainAllAccessField = reset($terms)->hasField(\Drupal\domain_access\DomainAccessManagerInterface::DOMAIN_ACCESS_ALL_FIELD);

    if ($hasDomainAccessField) {
      $terms = array_filter(
        $terms,
        function (\Drupal\taxonomy\TermInterface $term) use ($hasDomainAllAccessField, $domain_id) {
          $assignedDomains = array_map(
            function ($assignment) {
              return $assignment['target_id'];
            },
            $term->get(\Drupal\domain_access\DomainAccessManagerInterface::DOMAIN_ACCESS_FIELD)->getValue()
          );

          $accessOnAllDomains = $hasDomainAllAccessField &&
            (bool) $term->get(\Drupal\domain_access\DomainAccessManagerInterface::DOMAIN_ACCESS_ALL_FIELD)->getString();

          if ($accessOnAllDomains || in_array($domain_id, $assignedDomains)) {
            return TRUE;
          }
          return FALSE;
        }
      );
    }
    return $terms;
  }

  /**
   * Sorts all terms and simplifies them to required form
   *
   * @param Array $terms
   *  The terms that are going to be formed and simplified. (standartCategories, projectContributionType etc)
   * @return Array $list
   */
  protected function sortAndSimplifyTerms($terms) {
    $list = array_map(
      function ($term) {
        return [
          'id' => $term->id(),
          'name' => $term->label(),
          'description' => $term->getDescription(),
          'weight' => $term->getWeight(),
        ];
      },
      $terms
    );
    // sort the items according to weight.
    uasort($list, function ($term1, $term2) {
      return $term1['weight'] <=> $term2['weight'];
    });

    return $list;
  }

  /**
   * Helper function to retrieve a list of assigned terms to a proceeding.
   *
   * @param String $vocab
   *   The vocabulary ID
   * @param array $include_fields
   *   An array of fields to include from the terms fetched in the form ['fieldname' => function () { return $value; }]
   * @param array|NULL $assigned_ids
   *   Array holding the assigned term ids
   * @param String $flatten_to_property
   *   Should a term get flattened to a specific property?
   *
   * @return array
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function getAssignedTerms($vocab, array $include_fields, $assigned_ids, $flatten_to_property = FALSE) {
    $definedTerms = $this->getTermList($vocab, $include_fields, TRUE);

    $assignedTerms = array_filter(
      $definedTerms,
      function ($key) use ($assigned_ids) {
        return in_array($key, is_array($assigned_ids) ? array_filter($assigned_ids) : []);
      },
      ARRAY_FILTER_USE_KEY
    );

    return $flatten_to_property
      ? array_map(function ($term) use ($flatten_to_property) {
        return $term[$flatten_to_property];
      }, $assignedTerms)
      : $assignedTerms;
  }

  /**
   * @return \Drupal\Core\Entity\EntityStorageInterface
   */
  abstract protected function getTermStorage();

  /**
   * @return Boolean
   */
  abstract protected function isDomainModuleInstalled();

  /**
   * @return String
   */
  abstract protected function getActiveDomain();
}
