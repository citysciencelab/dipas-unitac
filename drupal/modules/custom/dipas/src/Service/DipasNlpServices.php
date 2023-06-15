<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html
 *   GPL-2.0-or-later
 */

namespace Drupal\dipas\Service;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\State\StateInterface;
use Drupal\dipas\Plugin\ResponseKey\DateTimeTrait;
use Drupal\masterportal\DomainAwareTrait;
use Drupal\node\NodeInterface;
use GuzzleHttp\ClientInterface;
use \Exception;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\dipas\Plugin\ResponseKey\RetrieveRatingTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\dipas\Plugin\ResponseKey\RetrieveCommentsTrait;

/**
 * Class DipasNlpServices.
 *
 * @package Drupal\dipas\Service
 */
class DipasNlpServices implements DipasNlpServicesInterface {

  use RetrieveRatingTrait;
  use RetrieveCommentsTrait;
  use DateTimeTrait;
  use DomainAwareTrait;

  /**
   * The DIPAS configuration service.
   *
   * @var \Drupal\dipas\Controller\DipasConfig
   */
  protected $dipasConfig;

  /**
   * Custom logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * The guzzle ClientInterface.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $guzzle;

  /**
   * Drupal's key-value-storage.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Custom service to easy the handling of entities.
   *
   * @var \Drupal\dipas\Service\EntityServicesInterface
   */
  protected $entityServices;

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * @var int
   */
  protected $node_id;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $commentStorage;

  /**
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Contains a string with a subdomain suffix for state variables.
   *
   * @var string
   */
  protected $domainSuffix;

  /**
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * DipasNlpServices constructor.
   *
   * @param \Drupal\dipas\Service\DipasConfigInterface $config
   *   Custom config service.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   Custom logger channel.
   * @param \GuzzleHttp\ClientInterface $guzzle
   *   The guzzle ClientInterface.
   * @param \Drupal\Core\State\StateInterface $state
   *   Drupal's key-value-storage.
   * @param \Drupal\dipas\Service\EntityServicesInterface $entity_services
   *   Drupal's entity type manager service.
   * @param \Drupal\Core\Database\Connection $db_connection
   *   Drupal database.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Drupal's entity type manager service.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   Drupal's date formatter service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *  The currently processed request.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(
    DipasConfigInterface $config,
    LoggerChannelInterface $logger,
    ClientInterface $guzzle,
    StateInterface $state,
    EntityServicesInterface $entity_services,
    Connection $db_connection,
    EntityTypeManagerInterface $entity_type_manager,
    DateFormatterInterface $date_formatter,
    RequestStack $request_stack
  ) {
    $this->dipasConfig = $config;
    $this->logger = $logger;
    $this->guzzle = $guzzle;
    $this->state = $state;
    $this->entityServices = $entity_services;
    $this->database = $db_connection;
    $this->commentStorage = $entity_type_manager->getStorage('comment');
    $this->dateFormatter = $date_formatter;
    $this->currentRequest = $request_stack->getCurrentRequest();

    $this->domainSuffix = sprintf(':%s', $this->getActiveDomain());
  }

  /**
   * {@inheritdoc}
   */
  protected function getDateFormatter() {
    return $this->dateFormatter;
  }

  /**
   * {@inheritdoc}
   */
  public function executeNlpScoresProcessing(array $contribution_nodes) {
    // Only run service when it is enabled and the previous request has finished.
    if (
      $this->dipasConfig->get('NLPSettings.enabled') === TRUE &&
      $this->dipasConfig->get('NLPSettings.enable_score_service') === TRUE &&
      $this->state->get(sprintf('dipas.nlp.score.status%s', $this->domainSuffix)) !== 'Processing'
    ) {
      return $this->executeNlp(
        $contribution_nodes,
        sprintf('dipas.nlp.score.status%s', $this->domainSuffix),
        sprintf('dipas.nlp.score.result%s', $this->domainSuffix),
        $this->dipasConfig->get('NLPSettings.score_service_basis_url'),
        'getScoreServiceHeader',
        'getScoreServiceBody',
        'score_service'
      );
    }
  }

  /**
   * Creates the configuration header, relevant for the NLP Score Service.
   *
   * @return array
   *   Array holding the configuration header.
   */
  protected function getScoreServiceHeader() {
    if (!empty($stoplistString = $this->dipasConfig->get('NLPSettings.score_stoplist'))) {
      $stoplist = preg_split('/\s*,\s*/', trim($stoplistString));
    }
    else {
      $stoplist = [];
    }

    return [
      'contentScore' => $this->dipasConfig->get('NLPSettings.enable_score_service_content_score'),
      'responseScore' => $this->dipasConfig->get('NLPSettings.enable_score_service_response_score'),
      'mutualityScore' => $this->dipasConfig->get('NLPSettings.enable_score_service_mutuality_score'),
      'relevanceScore' => $this->dipasConfig->get('NLPSettings.enable_score_service_relevance_score'),
      'sentimentScore' => $this->dipasConfig->get('NLPSettings.enable_score_service_sentiment_score'),
      'language' => 'de',
      'stoplist' => $stoplist,
    ];
  }

  /**
   * Creates the body, relevant for the NLP Score Service.
   *
   * @param array $contribution_nodes
   *   The set of contributions that should get analyzed by the NLP service.
   *
   * @return object
   *   Object holding the body for of the analysis.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function getScoreServiceBody(array $contribution_nodes) {
    $data_body = [];

    foreach ($contribution_nodes as $node) {

      // Retrieve ratings for node.
      $this->node_id = $node->id();
      $rating = $this->getRating();

      // Set body for this node for score service request.
      $node_data = [
        'id' => $node->id(),
        'title' => $node->label(),
        'commentsNumber' => $this->countCommentsForEntity($node),
        'votingPro' => $rating['upVotes'],
        'votingContra' => $rating['downVotes'],
        'body' => $node->get('field_text')->first()->getString(),
      ];

      $data_body[] = $node_data;
    }

    return $data_body;
  }

  /**
   * {@inheritdoc}
   */
  public function executeNlpClusteringProcessing(array $contribution_nodes, $filterid = '') {
    // Only run service when it is enabled and the previous request has finished.
    if (
      $this->dipasConfig->get('NLPSettings.enabled') === TRUE &&
      $this->dipasConfig->get('NLPSettings.enable_clustering') === TRUE &&
      $this->state->get(sprintf('dipas.nlp.clustering.status%s', $this->domainSuffix)) !== 'Processing'
    ) {
      return $this->executeNlp(
        $contribution_nodes,
        sprintf('dipas.nlp.clustering.status%s', $this->domainSuffix),
        sprintf('dipas.nlp.clustering.result%s%s', ($filterid ? "|$filterid" : ''), $this->domainSuffix),
        $this->dipasConfig->get('NLPSettings.clustering_service_basis_url'),
        'getClusteringServiceHeader',
        'getServiceBody',
        'clustering_service'
      );
    }
  }

  /**
   * Creates the configuration header, relevant for the NLP Clustering Service.
   *
   * @return array
   *   Array holding the configuration header.
   */
  protected function getClusteringServiceHeader() {
    if (!empty($stoplistString = $this->dipasConfig->get('NLPSettings.cluster_stoplist'))) {
      $stoplist = preg_split('/\s*,\s*/', trim($stoplistString));
    }
    else {
      $stoplist = [];
    }

    return [
      'clusterCount' => $this->dipasConfig->get('NLPSettings.cluster_count'),
      'language' => 'de',
      'stoplist' => $stoplist,
    ];
  }

  /**
   * Creates the body, relevant for the NLP Clustering Service & NLP Wordcloud Service.
   *
   * @param array $contribution_nodes
   *   The set of contributions that should get analyzed by the NLP service.
   *
   * @return object
   *   Object holding the body for of the analysis.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function getServiceBody(array $contribution_nodes) {
    $data_body = [];

    foreach ($contribution_nodes as $node) {

      // Retrieve ratings for node.
      $this->node_id = $node->id();
      $rating = $this->getRating();

      // Set body for this node for score service request.
      $node_data = [
        'id' => $node->id(),
        'title' => $node->label(),
        'body' => $node->get('field_text')->first()->getString(),
      ];

      $data_body[] = $node_data;
    }

    return $data_body;
  }

  /**
   * {@inheritdoc}
   */
  public function executeNlpWordcloudProcessing(array $contribution_nodes, $filterid = '') {
    // Only run service when it is enabled and the previous request has finished.
    if (
      $this->dipasConfig->get('NLPSettings.enabled') === TRUE &&
      $this->dipasConfig->get('NLPSettings.enable_wordcloud') === TRUE &&
      $this->state->get(sprintf('dipas.nlp.wordcloud.status%s', $this->domainSuffix)) !== 'Processing'
    ) {
      return $this->executeNlp(
        $contribution_nodes,
        sprintf('dipas.nlp.wordcloud.status%s', $this->domainSuffix),
        sprintf('dipas.nlp.wordcloud.result%s%s', ($filterid ? "|$filterid" : ''), $this->domainSuffix),
        $this->dipasConfig->get('NLPSettings.wordcloud_service_basis_url'),
        'getWordcloudServiceHeader',
        'getServiceBody',
        'wordcloud_service'
      );
    }
  }

  /**
   * Creates the configuration header, relevant for the NLP Wordcloud Service.
   *
   * @return array
   *   Array holding the configuration header.
   */
  protected function getWordcloudServiceHeader() {
    if (!empty($stoplistString = $this->dipasConfig->get('NLPSettings.wordcloud_stoplist'))) {
      $stoplist = preg_split('/\s*,\s*/', trim($stoplistString));
    }
    else {
      $stoplist = [];
    }

    return [
      'num' => $this->dipasConfig->get('NLPSettings.wordcloud_count'),
      'language' => 'de',
      'stoplist' => $stoplist,
      'dictionary' => $this->dipasConfig->get('NLPSettings.wordcloud_dictionary'),
    ];
  }

  /**
   * Returns the statistics from an NLP service for a set of contributions.
   *
   * @param array $contribution_nodes
   *   The set of contributions that should get analyzed by the NLP service.
   * @param string $status_key
   *   The status key to request the service status out of drupal state.
   * @param string $result_key
   *   The result key where to save the result of the request in drupal state.
   * @param string $basis_url
   *   The basis url where to find the service that shall be requested.
   * @param string $header_function
   *   The function that creates the header for this service-request.
   * @param string $body_function
   *   The function that creates the body for this service-request.
   * @param string $service_name
   *   The name of the service being executed.
   *
   * @return object
   *   Object holding the results of the analysis.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  protected function executeNlp(
    array $contribution_nodes,
    string $status_key,
    string $result_key,
    string $basis_url,
    string $header_function,
    string $body_function,
    string $service_name
  ) {
    $response = [];

    $this->state->set($status_key, 'Processing');

    try {
      if ($contribution_nodes && !empty($contribution_nodes)) {

        $header = $this->$header_function();
        $body = $this->$body_function($contribution_nodes);

        $nlp_score = $this->guzzle->request(
          'POST',
          $basis_url,
          [
            'json' => [
              'configuration' => $header,
              'documents' => $body,
            ],
          ]
        );

        $response = ($result = json_decode($nlp_score->getBody()->__toString())) ? $result : [];

        $timestamp = time();
        $this->state->set($status_key, 'Finished');
        $this->state->set(sprintf('dipas.nlp.%s.last_run_time%s', $service_name, $this->domainSuffix), $timestamp);
      }
    }
    catch (Exception $e) {
      $this->logger->error("Unexpected error: {$e->getCode()} - {$e->getMessage()}");
      $this->state->set($status_key, 'Failed');

      return (object) [
        'message' => 'An unexpected error occurred.',
      ];
    }

    $timestamp = time();

    $this->state->delete($result_key);

    if (isset($response) && isset($response->results) && is_array($response->results)) {
      $element = [
        'timestamp' => $timestamp,
        'result' => $response->results,
        'contained_nids' => array_map(
          function (NodeInterface $node) {
            return $node->id();
          },
          $contribution_nodes
        ),
      ];
    }
    elseif (isset($response)) {
      $element = [
        'timestamp' => $timestamp,
        'result' => $response,
      ];
    }

    if (isset($element)) {
      $this->state->set($result_key, $element);
      return $element;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityTypeId() {
    return 'node';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityId() {
    return $this->node_id;
  }

  /**
   * {@inheritdoc}
   */
  protected function getDatabase() {
    return $this->database;
  }

  /**
   * {@inheritdoc}
   */
  protected function getCommentStorage() {
    return $this->commentStorage;
  }

  /**
   * Returns the clustering data (singleton).
   *
   * @param array $filter
   *   Any filter parameters as a key-value array
   *
   * @return array
   */
  public function getNLPClusterData(array $filter = []) {
    // Determine the id for the filter criteria given.
    $filterid = $this->getFilterIdString($filter);

    // Try to get data from previous runs within the same thread.
    $clusterdata = drupal_static(
      sprintf(
        'dipas_nlp_clustering_values%s',
        $filterid ? "|$filterid" : ''
      ),
      FALSE
    );

    // If no previous runs were made, prepare the data.
    if ($clusterdata === FALSE) {
      // Construct a key, under which the data is stored in the state service.
      $statekey = sprintf('dipas.nlp.clustering.result%s%s', ($filterid ? "|$filterid" : ''), $this->domainSuffix);

      // Try to get previously stored data from the state service.
      $clusterdata_raw = $this->state->get($statekey);

      // Determine contribution ids matching the criteria given.
      $nids = $this->getContributionNodeIDs($filter);

      // When there is no previously stored data available
      // (or the data stored does not match the given filter)
      // start a new request to the NLP service.
      if (
        !is_array($clusterdata_raw) ||
        (
          is_array($clusterdata_raw) &&
          !empty($filter) &&
          !empty(array_diff($nids, $clusterdata_raw['contained_nids']))
        )
      ) {
        if (!empty($nids)) {
          // Load the contribution nodes for this request.
          $nodes = $this->entityServices->loadEntities('node', $nids);
          $clusterdata_raw = $this->executeNlpClusteringProcessing($nodes, $filterid);
          $clusterdata = is_array($clusterdata_raw) ? $clusterdata_raw['result'] : [];
        }
        else {
          $clusterdata = (object) [
            'message' => 'No clusters match the criteria selected.',
          ];
        }

      }
      else {
        // Extract the wordcloud data from the result.
        $clusterdata = $clusterdata_raw['result'];
      }
    }

    return $clusterdata;
  }

  /**
   * Returns the names of the available clusters.
   *
   * @param array $filter
   *   Any filter parameters as a key-value array
   *
   * @return array
   */
  public function getClusterNames(array $filter = []) {
    $clusterdata = $this->getNLPClusterData($filter);
    return is_array($clusterdata)
      ? array_map(
        function ($cluster) {
          return $cluster->title;
        },
        $clusterdata
      )
      : $clusterdata;
  }

  /**
   * Returns ready-to-use options for available clusters.
   *
   * @param array $filter
   *   Any filter parameters as a key-value array
   *
   * @return array
   */
  public function getClusterOptions(array $filter = []) {
    $clusters = $this->getClusterNames($filter);
    return is_array($clusters) ? array_combine($clusters, $clusters) : [];
  }

  /**
   * Returns the content IDs assiciated with a given cluster.
   *
   * @param string $cluster
   *   The cluster title.
   *
   * @return array
   */
  public function getIDsForCluster($clustertitle) {
    $clusterdata = $this->getNLPClusterData();
    $clusterdata = array_filter(
      $clusterdata,
      function ($cluster) use ($clustertitle) {
        return $cluster->title === $clustertitle;
      }
    );
    return reset($clusterdata)->ids;
  }

  /**
   * {@inheritdoc}
   */
  public function getClusterlist() {
    $clusters = $this->getClusterNames($this->currentRequest->query->all());
    $response = new JsonResponse($clusters);
    return $response;
  }

  /**
   * Returns the wordcloud data (singleton).
   *
   * @param array $filter
   *   Any filter parameters as a key-value array
   *
   * @return array
   */
  public function getNLPWordcloudData(array $filter = []) {
    // Determine the id for the filter criteria given.
    $filterid = $this->getFilterIdString($filter);

    // Try to get data from previous runs within the same thread.
    $wordclouddata = drupal_static(
      sprintf(
        'dipas_nlp_wordcloud_values%s',
        $filterid ? "|$filterid" : ''
      ),
      FALSE
    );

    // If no previous runs were made, prepare the data.
    if ($wordclouddata === FALSE) {

      // Construct a key, under which the data is stored in the state service.
      $statekey = sprintf('dipas.nlp.wordcloud.result%s%s', ($filterid ? "|$filterid" : ''), $this->domainSuffix);

      // Try to get previously stored data from the state service.
      $wordclouddata_raw = $this->state->get($statekey);

      // Determine contribution ids matching the criteria given.
      $nids = $this->getContributionNodeIDs($filter);

      // When there is no previously stored data available
      // (or the data stored does not match the given filter)
      // start a new request to the NLP service.
      if (
        !is_array($wordclouddata_raw) ||
        (
          is_array($wordclouddata_raw) &&
          !empty($filter) &&
          !empty(array_diff($nids, $wordclouddata_raw['contained_nids']))
        )
      ) {

        if (!empty($nids)) {
          // Load the contribution nodes for this request.
          $nodes = $this->entityServices->loadEntities('node', $nids);
          $wordclouddata_raw = $this->executeNlpWordcloudProcessing($nodes, $filterid);
          $wordclouddata = is_array($wordclouddata_raw) ? $wordclouddata_raw['result'] : $wordclouddata_raw;
        }
        else {
          $wordclouddata = (object) [
            'message' => 'No contributions match the criteria selected.',
          ];
        }

      }
      else {
        // Extract the wordcloud data from the result.
        $wordclouddata = $wordclouddata_raw['result'];
      }

    }
    return $wordclouddata;
  }

  /**
   * {@inheritdoc}
   *
   * TODO - actually implement any logic here when the wordcloud service has
   * actual data.
   */
  public function getWordcloud() {
    $wordclouddata = $this->getNLPWordcloudData($this->currentRequest->query->all());
    $response = new JsonResponse($wordclouddata);
    return $response;
  }

  /**
   * @param array $filter
   *   Filter options for the nodes as a key-value array.
   *
   * @return string
   *   A string representing the given filter criteria.
   */
  protected function getFilterIdString(array $filter = []) {
    // Prepare a possible filter identifier.
    ksort($filter);
    array_walk(
      $filter,
      function (&$value, $key) {
        $value = sprintf('%s:%s', $key, $value);
      }
    );
    return implode('|', $filter);
  }

  /**
   * Returns node ids of contribution nodes corresponding to given filter
   * criterias.
   *
   * @param array $filter
   *   Filter options for the nodes as a key-value array.
   *
   * @return array
   *   Array of node ids matching the filter criteria.
   */
  protected function getContributionNodeIDs(array $filter = []) {
    // Determine the fields available on the contribution entities.
    $fields = $this->entityServices->getEntityTypeBundleFields('node', 'contribution');

    // Determine all node ids of contribution nodes matching this request.
    $query = $this->entityServices->getEntityStorageInterface('node')->getQuery();
    $query->condition('type', 'contribution', '=');
    $query->condition('status', '1', '=');
    if (!empty($filter)) {
      foreach ($this->currentRequest->query->getIterator() as $field => $value) {
        // Only add query conditions for existing fields...
        if (isset($fields[$field])) {
          $query->condition($field, $value, '=');
        }
      }
    }
    if ($this->isDomainModuleInstalled()) {
      $this->makeEntityQueryDomainSensitive($query);
    }
    $nids = $query->execute();

    // Was there a cluster set as a filter element?
    if ($this->currentRequest->query->has('cluster')) {
      $clusterids = $this->getIDsForCluster($this->currentRequest->query->get('cluster'));
      // Filter the IDs determined by the IDs actually contained in the given cluster.
      $nids = array_filter(
        $nids,
        function ($nid) use ($clusterids) {
          return in_array($nid, $clusterids);
        }
      );
    }

    return $nids;
  }

}
