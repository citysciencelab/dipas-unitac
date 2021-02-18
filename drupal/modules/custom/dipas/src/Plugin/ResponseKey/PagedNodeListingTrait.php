<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\ResponseKey;

use Drupal\Core\Url;

trait PagedNodeListingTrait {

  use NodeListingTrait {
    NodeListingTrait::getNodes as protected getNodesUnpaged;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginResponse() {
    return [
      'totalNodes' => $this->getCount(),
      'pager' => [
        'itemsPerPage' => $this->itemsPerPage(),
        'currentPage' => $this->getCurrentPage(),
        'totalPages' => $this->getTotalPages(),
        'lastPage' => !isset($this->getLinks()['next']),
      ],
      'filters' => (object) $this->getFiltersApplied(),
      'sort' => [
        'field' => $this->getSortingField(),
        'direction' => $this->getSortingDirection(),
      ],
      'links' => (object) $this->getLinks(),
      'nodes' => $this->getNodes(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getResponseKeyCacheTags() {
    $cachetags = [];
    foreach ($this->getNodes() as $node) {
      $cachetags[] = "node:{$node->nid}";
    }
    return $cachetags;
  }

  /**
   * Returns a list of the nodes of the currently requested page.
   *
   * @return array[]
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Exception
   */
  protected function getNodes() {
    static $nodes = NULL;
    if ($nodes === NULL) {
      $query = $this->getQuery();

      // Should this query be a paged query?
      if ($this->itemsPerPage() !== PagedNodeListingInterface::INFINITE_ITEMS_VALUE) {
        $query->range(($this->getCurrentPage() - 1) * $this->itemsPerPage(), $this->itemsPerPage());
      }

      // Convert the created timestamp to an ISO 8601 UTC date string
      $nodes = $query->execute()->fetchAll();
      array_walk($nodes, function (&$node) {
        $node->created = $this->convertTimestampToUTCDateTimeString($node->created, FALSE);
      });

      // Let the concrete implementation of this trait also do it's thing.
      $this->postProcessNodeData($nodes);
    }
    return $nodes;
  }

  /**
   * Returns the number of nodes per page.
   *
   * @return int
   */
  protected function itemsPerPage() {
    static $itemsPerPage = NULL;
    if ($itemsPerPage === NULL) {
      $urlLimit = $this->currentRequest->query->get('itemsPerPage');
      if ($urlLimit !== NULL && is_numeric($urlLimit)) {
        $itemsPerPage = (int) $urlLimit;
        if ($itemsPerPage < 1) {
          $itemsPerPage = PagedNodeListingInterface::INFINITE_ITEMS_VALUE;
        }
      }
      else {
        $itemsPerPage = PagedNodeListingInterface::DEFAULT_NODES_PER_PAGE;
      }
    }
    return $itemsPerPage;
  }

  /**
   * Returns the number of the current page.
   *
   * @return int
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getCurrentPage() {
    $requested = $this->currentRequest->query->get('page');
    $currentPage = $requested !== NULL ? (int) $requested : 1;
    return $currentPage <= $this->getTotalPages() ? $currentPage : $this->getTotalPages();
  }

  /**
   * Returns the total number of available pages.
   *
   * @return int
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getTotalPages() {
    return $this->itemsPerPage() !== static::INFINITE_ITEMS_VALUE
      ? ((int) ceil($this->getCount()/$this->itemsPerPage()) ?: 1)
      : 1;
  }

  /**
   * Returns a list of pager links.
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getLinks() {
    $links = [];
    if ($this->itemsPerPage() !== static::INFINITE_ITEMS_VALUE) {
      $queryArgs = $this->currentRequest->query->all();
      if ($this->getCurrentPage() > 1) {
        $queryArgs['page'] = $this->getCurrentPage() - 1;
        $links['last'] = Url::fromRoute(
          'dipas.restapi.endpoint',
          ['key' => $this->pluginDefinition['id']],
          [
            'absolute' => TRUE,
            'query' => $queryArgs,
          ]
        )->toString();

      }
      if ($this->getCurrentPage() < $this->getTotalPages()) {
        $queryArgs['page'] = $this->getCurrentPage() + 1;
        $links['next'] = Url::fromRoute(
          'dipas.restapi.endpoint',
          ['key' => $this->pluginDefinition['id']],
          [
            'absolute' => TRUE,
            'query' => $queryArgs,
          ]
        )->toString();
      }
    }
    return $links;
  }

  /**
   * Returns the total number of contributions.
   *
   * @return int
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getCount() {
    static $count = NULL;
    if ($count === NULL) {
      $count = $this->getQuery()->countQuery()->execute()->fetchCol()[0];
    }
    return $count;
  }

  /**
   * Returns a flattened version of filters applied.
   *
   * @return array
   */
  protected function getFiltersApplied() {
    $filtersApplied = [];
    foreach ($this->getConditions() as $filter) {
      list(, $field) = explode('.', $filter['field']);
      $filtersApplied[$field] = $filter['value'];
    }
    return $filtersApplied;
  }

  /**
   * Postprocess function for nodes loaded.
   *
   * @param array $nodes
   *   The array containing the loaded node information.
   *
   * @return void
   *   Function manipulates array directly by reference.
   */
  abstract protected function postProcessNodeData(array &$nodes);

  /**
   * Formats a given DateTime object into an UTC datetime string.
   *
   * @param int $timestamp
   * @param boolean $isUTC
   *
   * @return string
   * @throws \Exception
   */
  abstract protected function convertTimestampToUTCDateTimeString($timestamp, $isUTC);

}
