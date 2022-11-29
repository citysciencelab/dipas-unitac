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
   * @param array $include_fields
   *   An array of fields to include from the terms fetched in the form ['fieldname' => function () { return $value; }]
   * @param Boolean $allDomains
   *   Boolean indicating if the list should get filtered by domain affiliation
   *
   * @return array
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function getTermList($vocab, array $include_fields = [], $allDomains = FALSE) {
    $termlist = drupal_static('dipas_termlist', []);
    $fields = array_keys($include_fields);
    sort($fields);

    if (!isset($termlist[$vocab][$allDomains ? 'all' : $this->getActiveDomain()][count($fields) ? implode(",", $fields) : "--nofields--"])) {

      /* @var \Drupal\taxonomy\TermInterface[] $terms */
      $terms = $this->getTermStorage()->loadByProperties(['vid' => $vocab]);

      if (
        count($terms) &&
        $this->isDomainModuleInstalled() &&
        !$allDomains
      ) {
        $hasDomainAccessField = reset($terms)->hasField(\Drupal\domain_access\DomainAccessManagerInterface::DOMAIN_ACCESS_FIELD);
        $hasDomainAllAccessField = reset($terms)->hasField(\Drupal\domain_access\DomainAccessManagerInterface::DOMAIN_ACCESS_ALL_FIELD);

        if ($hasDomainAccessField) {
          $terms = array_filter(
            $terms,
            function (\Drupal\taxonomy\TermInterface $term) use ($hasDomainAccessField, $hasDomainAllAccessField) {
              $assignedDomains = array_map(
                function ($assignment) {
                  return $assignment['target_id'];
                },
                $term->get(\Drupal\domain_access\DomainAccessManagerInterface::DOMAIN_ACCESS_FIELD)->getValue()
              );

              $accessOnAllDomains = $hasDomainAllAccessField &&
                (bool) $term->get(\Drupal\domain_access\DomainAccessManagerInterface::DOMAIN_ACCESS_ALL_FIELD)->getString();

              if ($accessOnAllDomains || in_array($this->getActiveDomain(), $assignedDomains)) {
                return TRUE;
              }

              return FALSE;
            }
          );
        }
      }

      $list = array_map(
        function ($term) {
          return [
            'id' => $term->id(),
            'name' => $term->label(),
            'description' => $term->getDescription()
          ];
        },
        $terms
      );

      foreach ($include_fields as $field => $preprocess) {
        array_walk($list, function (&$termdata, $tid) use ($terms, $field, $preprocess) {
          $termdata[$field] = $preprocess($terms[$tid]->get($field)->first());
        });
      }

      $termlist[$vocab][$allDomains ? 'all' : $this->getActiveDomain()][count($fields) ? implode(",", $fields) : "--nofields--"] = $list;
    }

    return $termlist[$vocab][$allDomains ? 'all' : $this->getActiveDomain()][count($fields) ? implode(",", $fields) : "--nofields--"];
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
      ? array_map(function ($term) use ($flatten_to_property) { return $term[$flatten_to_property]; }, $assignedTerms)
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
