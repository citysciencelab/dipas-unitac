<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\PDSResponse;

use stdClass;
use Drupal\Core\Url;
use Drupal\masterportal\GeoJSONFeature;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\dipas\Plugin\ResponseKey\RetrieveRatingTrait;
use Drupal\dipas\Plugin\ResponseKey\RetrieveCommentsTrait;
use Drupal\dipas\Plugin\ResponseKey\DateTimeTrait;

/**
 * Class PDSContributionList.
 *
 * @PDSResponse(
 *   id = "pdscontributionlist",
 *   description = @Translation("Returns a list of contributions currently contained in the database following the PDS standard."),
 *   requestMethods = {
 *     "GET",
 *   },
 *   isCacheable = true
 * )
 *
 * @package Drupal\dipas\Plugin\PDSResponse
 */
class PDSContributionList extends PDSResponseBase {

  use RetrieveRatingTrait;
  use RetrieveCommentsTrait;
  use DateTimeTrait;
  use EnrichPDSCommentsTrait;

  /**
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Drupal's node storage service.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * Drupal's taxonomy term storage service.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $termStorage;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $commentStorage;

  /**
   * @var int
   */
  protected $node_id;

  /**
   * {@inheritdoc}
   */
  public function setAdditionalDependencies(ContainerInterface $container) {
    $this->dateFormatter = $container->get('date.formatter');

    $this->nodeStorage = $this->entityTypeManager->getStorage('node');
    $this->termStorage = $this->entityTypeManager->getStorage('taxonomy_term');
    $this->commentStorage = $this->entityTypeManager->getStorage('comment');
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
  protected function getCommentStorage() {
    return $this->commentStorage;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginResponse() {
    $features = [];
    $domain_id = $this->domainModulePresent ? $this->currentRequest->attributes->get('proj_ID'): 'default';

    $query = $this->nodeStorage->getQuery()
                  ->condition('type', 'contribution', '=')
                  ->condition('status', 1, '=')
                  ->condition('field_domain_access', $domain_id, '=');

    if ($this->currentRequest->attributes->get('contr_ID') !== '0') {
      $query->condition('nid', $this->currentRequest->attributes->get('contr_ID'), '=');
    }

     // Load all published contributions.
    $nodeIds = $query->execute();
    $contributionNodes = $this->nodeStorage->loadMultiple($nodeIds);

    // Cycle over the contributions.
    foreach ($contributionNodes as $node) {

      // Get the location of the current node.
      $geodata = ($fieldValue = $node->get('field_geodata')->first()) ? $fieldValue->getString() : '';
      $dipasLocated = true;

      // find non-localized contributions to add project area gemetry instead.
      if (empty($geodata) || ($geodata = json_decode($geodata)) === null || count((array) $geodata) === 0) {
        $dipasLocated = false;
        $geodata = json_decode($this->dipasConfig->get('ProjectArea/project_area'));

        if (!$geodata || !isset($geodata->coordinates) || count($geodata->coordinates) === 0) {
          $geodata = new stdClass;
          $geodata->type = "Point";
          $geodata->coordinates = [0.0,0.0];
        }
      }

      // Extract taxonomy terms of this node.
      $taxonomyString = [];
      foreach (['field_category', 'field_rubric'] as $field) {
        if (
          ($fieldvalue = $node->get($field)->first()) &&
          $term = $this->termStorage->load($fieldvalue->getString())
        ) {
          $taxonomyString[str_replace('field_', '', $field)] = $term->getName();
        }
        else {
          $taxonomyString[str_replace('field_', '', $field)] = 'none';
        }
      }

      // retrieve ratings for node
      $this->node_id = $node->id();
      $rating = $this->getRating();

      //retrieve comments for node if required
      $comments = false;
      if (strpos($this->currentRequest->getPathInfo(), 'commentedcontributions') !== FALSE) {
        $comments = $this->loadCommentsForEntity($node, 'pds');

        $comments = $this->enrichComments($comments, 0, $this->node_id);
      }

      // select keywords chosen for node
      $keywordsSuggested = "";
      $keywordsSelected = "";

      // Construct the basic query object.
      /* @var \Drupal\Core\Database\Query\SelectInterface $keyword_query */
      $keyword_query = $this->getDatabase()->select('dipas_keywords', 'keyword')
        ->fields('keyword', array('keywords', 'selected_keywords'))
        ->condition('keyword.contribution_id', $this->node_id, '=');

      $keywords = $keyword_query->execute()->fetchAll();
      if ($keywords && count($keywords) > 0) {
        $keywordsSuggested = $keywords[0]->keywords;
        $keywordsSelected = $keywords[0]->selected_keywords;
      }

      // Create a feature container for the current contribution.
      $featureObject = new GeoJSONFeature();

       // Add the geolocation information to it.
      if (!empty($geodata->geometry)) {
        $featureObject->setGeometry($geodata->geometry);
      }
      else {
        $featureObject->setGeometry($geodata);
      }

      // Add the node information to it.
      $featureObject->addProperty('id', $node->id());
      $featureObject->addProperty('dateCreated', $this->convertTimestampToUTCDateTimeString($node->get('created')->getString(), FALSE));
      $replace_string = "://$domain_id.";
      $featureObject->addProperty('link', preg_replace('/:\/\//', $replace_string, preg_replace('/\/drupal\/.*$/', '/#', Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString())) . '/contribution/' . $node->id());
      $featureObject->addProperty('title', $node->label());
      $featureObject->addProperty('contributionType', $taxonomyString['rubric']);
      $featureObject->addProperty('contributionContent', $node->get('field_text')->first()->getString());
      $featureObject->addProperty('commentsNumber', $this->countCommentsForEntity($node));
      $featureObject->addProperty('category', $taxonomyString['category']);
      $featureObject->addProperty('votingPro', $rating['upVotes']);
      $featureObject->addProperty('votingContra', $rating['downVotes']);
      $featureObject->addProperty('keywordSuggested', $keywordsSuggested);
      $featureObject->addProperty('keywordPicked', $keywordsSelected);
      $featureObject->addProperty('belongToProject', $domain_id);
      $featureObject->addProperty('dipasLocated', $dipasLocated);
      $featureObject->addProperty('customAttribute', $this->getNLPScoresList($domain_id));

      if (is_array($comments)) {
        $featureObject->addProperty('commentedBy', $comments);
      }

      // Add the current node to the content container.
      $features[] = $featureObject;
    }

    return $features;
  }

  /**
   * {@inheritdoc}
   */
  protected function getResponseKeyCacheTags() {
    return [];
  }

  /**
   * Returns a list of all nlp scores relevant for this contribution.
   *
   * @param string $domain_id
   *   The id of the selected domain.
   *
   * @return array|false
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getNLPScoresList($domain_id) {
    $item_scores = (object) [
      'content' => '-',
      'relevance' => '-',
      'response' => '-',
      'mutuality' => '-',
      'sentiment' => '-',
    ];

    $nlp_scores = $this->state->get('dipas.nlp.score.result:'.$domain_id);

    if ($nlp_scores && $nlp_scores['result']) {

      $scores_index = array_search($this->node_id, array_column($nlp_scores['result'], 'id'));

      if ($scores_index !== FALSE) {
        $item_scores = $nlp_scores['result'][$scores_index]->scores;
      }
    }

    return (object) ['nlp_scores' => $item_scores];
  }

}

