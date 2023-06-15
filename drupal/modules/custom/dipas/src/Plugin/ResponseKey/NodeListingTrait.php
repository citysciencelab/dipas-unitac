<?php

namespace Drupal\dipas\Plugin\ResponseKey;

use Drupal\Core\Database\Query\ConditionInterface;
use Drupal\masterportal\DomainAwareTrait;

trait NodeListingTrait {
  use DomainAwareTrait;

  /**
   * Returns a list of the nodes of the currently requested page.
   *
   * @return array[]
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Exception
   */
  protected function getNodes() {
    static $nodes = NULL;

    if ($nodes === NULL) {
      $query = $this->getQuery();

      // Convert the created timestamp to an ISO 8601 UTC date string.
      $nodes = $query->execute()->fetchAll();
      array_walk($nodes, function (&$node) {
        $node->created = $this->convertTimestampToUTCDateTimeString($node->created, FALSE);
        $this->postProcessDataRow($node);
      });
      $this->postProcessNodes($nodes);
    }

    return $nodes;
  }

  /**
   * Initiates the basic database query object (singleton).
   *
   * @return \Drupal\Core\Database\Query\SelectInterface
   */
  protected function getBaseQuery() {
    /**
     * @var \Drupal\Core\Database\Query\SelectInterface
     */
    $query = drupal_static('dipasDatabaseQuery');

    if (is_null($query)) {
      $query = $this->getDatabase()->select('node', 'base')
        ->condition('base.type', $this->getNodeType(), '=')
        ->condition('attr.status', '1', '=');
    }

    return $query;
  }

  /**
   * Returns an entity query for published nodes of bundle contribution.
   *
   * @param bool $ignoreDomainBinding
   *   TRUE, if content from ALL Domains should get exported.
   *
   * @return \Drupal\Core\Database\Query\SelectInterface
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getQuery($ignoreDomainBinding = FALSE) {
    $query = $this->getBaseQuery();

    // Domain module integration.
    if (
      $this->isDomainModuleInstalled() &&
      $this->listingIsDomainSensitive() &&
      $activeDomain = $this->getActiveDomain()
    ) {
      // Add domain table joins.
      $query->addJoin('LEFT', 'node__field_domain_access', 'domain_access', 'base.type = domain_access.bundle AND base.nid = domain_access.entity_id AND base.vid = domain_access.revision_id');
      $query->addJoin('LEFT', 'node__field_domain_all_affiliates', 'domain_affiliates', 'base.type = domain_affiliates.bundle AND base.nid = domain_affiliates.entity_id AND base.vid = domain_affiliates.revision_id');

      // Add the domain conditions.
      $domainConditions = $query->orConditionGroup();
      $domainConditions->condition('domain_access.field_domain_access_target_id', $activeDomain, '=');
      $domainConditions->condition('domain_affiliates.field_domain_all_affiliates_value', '1', '=');
      $query->condition($domainConditions);
    }

    // Add basic fields.
    foreach ($this->getBaseFields() as $field => $definition) {
      $query->addField($definition['tablealias'], $field, $definition['fieldalias']);
    }

    // Join the node attributes table.
    $query->addJoin('LEFT', 'node_field_data', 'attr', 'base.type = attr.type AND base.nid = attr.nid AND base.vid = attr.vid');

    // Join the user tables.
    $query->addJoin('LEFT', 'users', 'usr', 'attr.uid = usr.uid');
    $query->addJoin('INNER', 'users_field_data', 'userdata', 'usr.uid = userdata.uid');

    // Join possible other tables as defined by the concrete implementation.
    foreach ($this->getJoins() as $definition) {
      $query->addJoin($definition['type'], $definition['table'], $definition['alias'], $definition['condition']);

      // Are there any fields that should get selected from the current join?
      if (isset($definition['fields'])) {
        foreach ($definition['fields'] as $field => $alias) {
          $query->addField($definition['alias'], $field, $alias);
        }
      }
    }

    // Add possible expression statements to the query.
    foreach ($this->getExpressions() as $expression => $alias) {
      $query->addExpression($expression, $alias);
    }

    // Are there any conditions that should be applied?
    foreach ($this->getConditions() as $condition) {
      if ($condition instanceof ConditionInterface) {
        $query->condition($condition);
      }
      else {
        $query->condition($condition['field'], $condition['value'], $condition['operator']);
      }
    }

    // Is there any grouping involved?
    if (!empty($this->getGroupBy())) {

      // First, add all basic groupBy fields (sql_mode ONLY_FULL_GROUP_BY)
      foreach ($this->getBaseFields() as $field => $definition) {
        $query->groupBy(sprintf('%s.%s', $definition['tablealias'], $field));
      }

      // Next, add the defined groupBy definitions of the concrete implementation.
      foreach ($this->getGroupBy() as $groupBy) {
        $query->groupBy($groupBy);
      }

    }

    // Apply sorting.
    $query->orderBy($this->getSortingField(), $this->getSortingDirection());

    // Should a limit be imposed?
    if ($this->getLimit() !== FALSE) {
      $query->range(0, $this->getLimit());
    }

    // Return the query.
    return $query;
  }

  /**
   * Returns a query condition group (OR).
   *
   * @return \Drupal\Core\Database\Query\ConditionInterface
   */
  protected function getOrConditionGroup() {
    return $this->getBaseQuery()->orConditionGroup();
  }

  /**
   * Returns a query condition group (AND).
   *
   * @return \Drupal\Core\Database\Query\ConditionInterface
   */
  protected function getAndConditionGroup() {
    return $this->getBaseQuery()->andConditionGroup();
  }

  /**
   * Returns the base fields to select.
   *
   * @return array
   */
  protected function getBaseFields() {
    return [
      'nid' => [
        'tablealias' => 'base',
        'fieldalias' => 'nid',
      ],
      'type' => [
        'tablealias' => 'base',
        'fieldalias' => 'type',
      ],
      'title' => [
        'tablealias' => 'attr',
        'fieldalias' => 'title',
      ],
      'created' => [
        'tablealias' => 'attr',
        'fieldalias' => 'created',
      ],
      'langcode' => [
        'tablealias' => 'attr',
        'fieldalias' => 'langcode',
      ],
      'name' => [
        'tablealias' => 'userdata',
        'fieldalias' => 'author',
      ],
    ];
  }

  /**
   * Should a limit be applied?
   *
   * @return int|bool
   */
  protected function getLimit() {
    return FALSE;
  }

  /**
   * Getter function for AND toggle function to set the node listing domain sensitivity.
   *
   * @param bool $isSensitive
   *   Set the listing to be domain sensitive or not.
   *
   * @return bool
   *   The domain sensitivity flag.
   */
  protected function listingIsDomainSensitive($isSensitive = NULL) {
    static $listingIsDomainsensitive;

    if ($listingIsDomainsensitive === NULL || $isSensitive !== NULL) {
      if ($this->isDomainModuleInstalled()) {
        if ($listingIsDomainsensitive === NULL && $isSensitive === NULL) {
          $listingIsDomainsensitive = TRUE;
        }
        else {
          $listingIsDomainsensitive = $isSensitive;
        }
      }
      else {
        $listingIsDomainsensitive = FALSE;
      }
    }

    return $listingIsDomainsensitive;
  }

  /**
   * Called on each data row returned by the query.
   *
   * @param array $row
   *   The currently processed data row
   *
   * @return void
   */
  protected function postProcessDataRow(&$row) {}

  /**
   * Acts on all rows returned by the database query.
   *
   * @param array $nodes
   *   The complete set of data rows resulted from the query.
   *
   * @return void
   */
  protected function postProcessNodes(array &$nodes) {}

  /**
   * Returns the node type to list.
   *
   * @return string
   */
  abstract protected function getNodeType();

  /**
   * Returns the database connection.
   *
   * @return \Drupal\Core\Database\Connection
   */
  abstract protected function getDatabase();

  /**
   * Returns the tables to join to the query.
   *
   * @return array
   *   Structured JOIN definitions.
   */
  abstract protected function getJoins();

  /**
   * Returns the expression statements to be added to the query.
   *
   * @return array
   */
  abstract public function getExpressions();

  /**
   * Returns additional conditions to be imposed on the query.
   *
   * @return array
   *   Structured conditions array.
   */
  abstract protected function getConditions();

  /**
   * Returns the groupBy definition of the concrete implementation.
   *
   * @return array
   */
  abstract protected function getGroupBy();

  /**
   * Returns the field name to sort on.
   *
   * @return string
   */
  abstract protected function getSortingField();

  /**
   * Returns the sorting direction.
   *
   * @return string
   *   ASC or DESC
   */
  abstract protected function getSortingDirection();

  /**
   * Formats a given DateTime object into an UTC datetime string.
   *
   * @param int $timestamp
   * @param bool $isUTC
   *
   * @return string
   *
   * @throws \Exception
   */
  abstract protected function convertTimestampToUTCDateTimeString($timestamp, $isUTC);

}
