<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Service;

interface DipasNlpServicesInterface {

  /**
   * Returns the score statistics from an NLP service for a set of contributions.
   *
   * @param array $contribution_nodes
   *   The set of contributions that should get analyzed by the NLP service.
   *
   * @return object
   *   Object holding the results of the analysis.
   */
  public function executeNlpScoresProcessing(array $contribution_nodes);

  /**
   * Returns the clustering from an NLP service for a set of contributions.
   *
   * @param array $contribution_nodes
   *   The set of contributions that should get analyzed by the NLP service.
   * @param string $filterid
    *   String identifying an applied filter to the nodes array
   *
   * @return object
   *   Object holding the results of the analysis.
   */
  public function executeNlpClusteringProcessing(array $contribution_nodes, $filterid = '');

   /**
    * Returns the wordcloud from an NLP service for a set of contributions.
    *
    * @param array $contribution_nodes
    *   The set of contributions that should get analyzed by the NLP service.
    * @param string $filterid
    *   String identifying an applied filter to the nodes array
    *
    * @return object
    *   Object holding the results of the analysis.
    */
  public function executeNlpWordcloudProcessing(array $contribution_nodes, $filterid = '');

  /**
   * Returns a list of clusters
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function getClusterlist();

  /**
   * Returns an URL to a wordcloud image.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function getWordcloud();

}
