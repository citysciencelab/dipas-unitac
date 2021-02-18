<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Service;

use Drupal\comment\CommentManagerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\dipas\Plugin\ResponseKey\ContributionDetailsTrait;
use Drupal\dipas\Plugin\ResponseKey\DateTimeTrait;
use Drupal\dipas\Plugin\ResponseKey\NodeListingTrait;
use Drupal\masterportal\DomainAwareTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

class DataExport implements DataExportInterface {

  use NodeListingTrait;
  use DateTimeTrait;
  use ContributionDetailsTrait {
    ContributionDetailsTrait::getJoins as protected traitJoins;
    ContributionDetailsTrait::getExpressions as protected traitExpressions;
    ContributionDetailsTrait::getGroupBy as protected traitGroupBy;
  }
  use StringTranslationTrait;
  use DomainAwareTrait;

  /**
   * @var string
   */
  protected $exportNodeType;

  /**
   * The Drupal database.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * @var \Drupal\csv_serialization\Encoder\CsvEncoder
   */
  protected $csvEncoder;

  /**
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $termStorage;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $commentStorage;

  /**
   * Export service constructor.
   *
   * @param \Drupal\Core\Database\Connection $db_connection
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   * @param \Symfony\Component\Serializer\Encoder\EncoderInterface $csv_encoder
   * @param \Drupal\Core\State\StateInterface $state
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(
    Connection $db_connection,
    DateFormatterInterface $date_formatter,
    EncoderInterface $csv_encoder,
    StateInterface $state,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->database = $db_connection;
    $this->dateFormatter = $date_formatter;
    $this->csvEncoder = $csv_encoder;
    $this->state = $state;
    $this->entityTypeManager = $entity_type_manager;

    $this->csvEncoder->setSettings([
      'delimiter' => ';',
      'enclosure' => '"',
      'escape_char' => '\\',
      'encoding' => 'utf8',
      'strip_tags' => FALSE,
      'trim' => TRUE,
    ]);
    $this->utcTimeZone = new \DateTimeZone('UTC');
    $this->drupalTimeZone = new \DateTimeZone(drupal_get_user_timezone());
    $this->nodeStorage = $this->entityTypeManager->getStorage('node');
    $this->termStorage = $this->entityTypeManager->getStorage('taxonomy_term');
    $this->commentStorage = $this->entityTypeManager->getStorage('comment');
  }

  /**
   * {@inheritdoc}
   */
  public function export($type) {
    $nlpScoreKey = 'dipas.nlp.score.result';
    if ($this->isDomainModuleInstalled()) {
      $nlpScoreKey .= sprintf(':%s', $this->getActiveDomain());
    }

    switch ($type) {
      case 'contributions':
        $this->exportNodeType = 'contribution';
        $categories = $this->getTaxonomyTermArray('categories');
        $rubrics = $this->getTaxonomyTermArray('rubrics');
        $nlp_scores = $this->state->get($nlpScoreKey);
        $nodes = $this->getQuery()->execute()->fetchAll();
        array_walk(
          $nodes,
          function (&$item) {
            $item = (array) $item;
          }
        );
        $nodes = array_map(
          function ($item) use ($categories, $rubrics, $nlp_scores) {
            $scores_index = array_search($item['nid'], array_column($nlp_scores['result'], 'id'));

            if ($scores_index !== FALSE) {
              $item_scores = $nlp_scores['result'][$scores_index]->scores;
            }
            else {
              $item_scores = (object) [
                'content' => '-',
                'relevance' => '-',
                'response' => '-',
              ];
            }

            return [
              'Node ID' => $item['nid'],
              'Category' => $categories[$item['category']],
              'Rubric' => $rubrics[$item['rubric']],
              'Created (UTC)' => $this->convertTimestampToUTCDateTimeString($item['created'], TRUE),
              'Rating' => $item['rating'],
              'Total votes' => $item['numVotes'],
              'Upvotes' => $item['upVotes'],
              'Downvotes' => $item['downVotes'],
              'Comments' => $item['comments'],
              'Title' => $item['title'],
              'Contributiontext' => str_replace(["\r\n", "\r", "\n"], ' ', $item['text']),
              'Location' => empty($item['geodata']) ? 'No' : 'Yes',
              'Contribution Type' => empty($item['geodata']) ? 'None' : $this->getContributionType($item['geodata']),
              'Selected Keywords' => $item['selectedKeywords'],
              'Suggested Keywords' => $item['suggestedKeywords'],
              'NLP Content Score BETA' => $item_scores->content,
              'NLP Relevance Score BETA' => $item_scores->relevance,
              'NLP Response Score BETA' => $item_scores->response,
            ];
          },
          $nodes
        );
        $data = $this->csvEncoder->encode($nodes, 'csv');
        $filename = 'contributions';
        break;

      case 'contribution_comments':
        $this->exportNodeType = 'contribution';
        $comments = $this->getEntityComments();
        $data = $this->csvEncoder->encode($comments, 'csv');
        $filename = 'contributioncomments';
        break;

      case 'conception_comments':
        $this->exportNodeType = 'conception';
        $comments = $this->getEntityComments();
        $data = $this->csvEncoder->encode($comments, 'csv');
        $filename = 'conceptioncomments';
        break;

      default:
        throw new NotFoundHttpException('The desired export type copuld not be found!');
    }

    $response = new Response();
    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s.csv"', $filename));
    // Sets the BOM which helps to display special characters in Excel.
    echo chr(hexdec('EF')) . chr(hexdec('BB')) . chr(hexdec('BF'));
    $response->setContent($data);
    return $response;
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
  protected function getDateFormatter() {
    return $this->dateFormatter;
  }

  /**
   * {@inheritdoc}
   */
  protected function getJoins() {
    if ($this->exportNodeType === 'contribution') {
      $joins = $this->traitJoins();
      $joins[] = [
        'type' => 'LEFT',
        'table' => 'node__field_text',
        'alias' => 'text',
        'condition' => 'base.type = text.bundle AND base.nid = text.entity_id AND base.vid = text.revision_id AND attr.langcode = text.langcode AND text.deleted = 0',
        'fields' => [
          'field_text_value' => 'text',
        ],
      ];
      $joins[] = [
        'type' => 'LEFT',
        'table' => 'node__field_geodata',
        'alias' => 'geodata',
        'condition' => 'base.type = geodata.bundle AND base.nid = geodata.entity_id AND base.vid = geodata.revision_id AND attr.langcode = geodata.langcode AND geodata.deleted = 0',
        'fields' => [
          'field_geodata_value' => 'geodata',
        ],
      ];
      $joins[] = [
        'type' => 'LEFT',
        'table' => 'dipas_keywords',
        'alias' => 'keywords',
        'condition' => 'base.nid = keywords.contribution_id',
        'fields' => [
          'selected_keywords' => 'selectedKeywords',
          'keywords' => 'suggestedKeywords',
        ],
      ];
      return $joins;
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getExpressions() {
    if ($this->exportNodeType === 'contribution') {
      return $this->traitExpressions();
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  protected function getGroupBy() {
    if ($this->exportNodeType === 'contribution') {
      $groupBy = $this->traitGroupBy();
      $groupBy[] = 'text.field_text_value';
      $groupBy[] = 'geodata.field_geodata_value';
      $groupBy[] = 'keywords.selected_keywords';
      $groupBy[] = 'keywords.keywords';
      return $groupBy;
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  protected function getConditions() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  protected function getSortingField() {
    return 'nid';
  }

  /**
   * {@inheritdoc}
   */
  protected function getSortingDirection() {
    return 'ASC';
  }

  /**
   * Returns an array of taxonomy terms keyed by tid.
   *
   * @param $vocab
   *
   * @return array
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getTaxonomyTermArray($vocab) {
    $terms = $this->termStorage->loadTree($vocab, 0, NULL, TRUE);
    $return = [];
    foreach ($terms as $term) {
      /* @var \Drupal\taxonomy\TermInterface $term */
      $return[$term->id()] = $term->label();
    }
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  protected function getNodeType() {
    return $this->exportNodeType;
  }

  /**
   * Fetches and flattens all comments to entities.
   *
   * @return array
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function getEntityComments() {
    $entities = $this->nodeStorage->loadMultiple(array_map(
      function ($item) {
        return $item->nid;
      },
      $this->getQuery()->execute()->fetchAll()
    ));

    $return = [];
    foreach ($entities as $entity) {
      /* @var \Drupal\Core\Entity\ContentEntityInterface $entity */
      $comments = $this->loadCommentsForEntity($entity);
      foreach ($comments as $commentStack) {
        $return[] = [
          'Contribution ID' => $entity->id(),
          'Comment ID' => $commentStack['cid'],
          'Comment Subject' => $commentStack['subject'],
          'Comment Text' => $commentStack['comment'],
          'created (UTC)' => $commentStack['created'],
        ];
        foreach ($commentStack['replies'] as $reply) {
          $return[] = [
            'Contribution ID' => $entity->id(),
            'Comment ID' => $reply['cid'],
            'Comment Subject' => $reply['subject'],
            'Comment Text' => $reply['comment'],
            'created (UTC)' => $reply['created'],
          ];
        }
      }
    }
    return $return;
  }

  /**
   * Returns stored comments recursively.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity for which to fetch comments.
   *
   * @return array
   *   The comments for the entity.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function loadCommentsForEntity(ContentEntityInterface $entity) {
    $commentList = [];
    $commentsField = $entity->getEntityTypeId() === 'node' ? 'field_comments' : 'field_replies';
    $comments = $this->commentStorage->loadThread($entity, $commentsField, CommentManagerInterface::COMMENT_MODE_FLAT);
    foreach ($comments as $comment) {
      /* @var \Drupal\comment\CommentInterface $comment */
      $this->cacheTags[] = sprintf('comment:%d', $comment->id());
      $this->commentCount++;
      $subject = ($subject = $comment->get('subject')->first()) ? $subject->getString() : '';
      $commentListEntry = [
        'cid' => $comment->id(),
        'subject' => $subject !== '(No subject)' ? $subject : '',
        'comment' => $comment->get('field_comment')->first()->getString(),
        'created' => $this->convertTimestampToUTCDateTimeString($comment->getCreatedTime(), FALSE),
        'replies' => $this->loadCommentsForEntity($comment),
      ];
      $commentList[] = $commentListEntry;
    }
    return $commentList;
  }

  /**
   * Returns a string containing the contribution type.
   *
   * @param $geodata
   *
   * @return string
   */
  protected function getContributionType($geodata) {
    $geodata = json_decode($geodata);

    if (($contributionType = $geodata->geometry->type) === 'LineString') {
      return 'Line';
    }
    else {
      return $contributionType;
    }
  }

}
