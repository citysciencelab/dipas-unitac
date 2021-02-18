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
 * Class PDSCommentsList.
 *
 * @PDSResponse(
 *   id = "pdscommentslist",
 *   description = @Translation("Returns a list of all comments to conceptions currently contained in the database following the PDS standard."),
 *   requestMethods = {
 *     "GET",
 *   },
 *   isCacheable = true
 * )
 *
 * @package Drupal\dipas\Plugin\PDSResponse
 */
class PDSCommentsList extends PDSResponseBase {

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
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $commentStorage;

  /**
   * {@inheritdoc}
   */
  public function setAdditionalDependencies(ContainerInterface $container) {
    $this->dateFormatter = $container->get('date.formatter');

    $this->nodeStorage = $this->entityTypeManager->getStorage('node');
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
  protected function getCommentStorage() {
    return $this->commentStorage;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginResponse() {
    $features = [];

    $type = '';
    $contr_ID = '';
    $domain_id = $this->domainModulePresent ? $this->currentRequest->attributes->get('proj_ID'): 'default';
    $dipasConfigDomain = $this->dipasConfig->getEditable(sprintf('dipas.%s.configuration', $domain_id));

    if ($this->currentRequest->attributes->get('type') === 'contributions' &&
      $this->currentRequest->attributes->get('contr_ID') !== '0'){
      $type = 'contribution';
      $contr_ID = $this->currentRequest->attributes->get('contr_ID');
    }
    else if ($this->currentRequest->attributes->get('type') === 'conception_comments'){
      $type = 'conception';
    }

    $query = $this->nodeStorage->getQuery()
                  ->condition('type', $type, '=')
                  ->condition('status', 1, '=')
                  ->condition('field_domain_access', $domain_id, '=');

    if ($contr_ID !== '') {
      $query->condition('nid', $contr_ID, '=');
    }

    // Load all published nodes of type 'conception' or 'contribution'.
    $nodeIds = $query->execute();
    $nodes = $this->nodeStorage->loadMultiple($nodeIds);

    foreach ($nodes as $node) {

      $this->node_id = $node->id();

       // Get the location of the current node if contribution or the project area if conception (or non-located contribution)
      $geodata = null;
      if ($type === 'contribution'){
        $geodata = ($fieldValue = $node->get('field_geodata')->first()) ? $fieldValue->getString() : '';
      }

      // find non-localized contributions to add project area gemetry instead.
      // for conceptions add project area to each comment
      if (empty($geodata) || ($geodata = json_decode($geodata)) === null || count((array) $geodata) === 0) {
        $geodata = json_decode($dipasConfigDomain->get('ProjectArea.project_area'));

        if (!$geodata || !isset($geodata->coordinates) || count($geodata->coordinates) === 0) {
          $geodata = new stdClass;
          $geodata->type = "Point";
          $geodata->coordinates = [0.0,0.0];
        }
      }

      //retrieve comments for node
      $comments = $this->loadCommentsForEntity($node, 'pds');

      // since this is a "comment" endpoint, only return data when comments are found
      if (count($comments) > 0){
        $comments = $this->enrichComments($comments, 0, $this->node_id);


        // Create a feature container for the current contribution.
        $featureObject = new GeoJSONFeature();

         // Add the geolocation information to it.
        if (!empty($geodata->geometry)) {
          $featureObject->setGeometry($geodata->geometry);
        }
        else {
          $featureObject->setGeometry($geodata);
        }

        $featureObject->addProperty('comments', $comments);

        $features[] = $featureObject;
      }
    }

    return $features;
  }

  /**
   * {@inheritdoc}
   */
  protected function getResponseKeyCacheTags() {
    return [];
  }

}

