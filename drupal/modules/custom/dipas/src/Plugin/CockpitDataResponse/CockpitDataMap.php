<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\CockpitDataResponse;

use Drupal\Core\Url;
use Drupal\masterportal\GeoJSONFeature;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\dipas\Plugin\ResponseKey\DateTimeTrait;
use Drupal\dipas\TaxonomyTermFunctionsTrait;
use Drupal\masterportal\DomainAwareTrait;

/**
 * Class CockpitDataMap.
 *
 * @CockpitDataResponse(
 *   id = "cockpitdatamap",
 *   description = @Translation("Returns proceeding statistics data for map showing proceeding areas on participation cockpit."),
 *   requestMethods = {
 *     "GET",
 *   },
 *   isCacheable = true,
 *   maxAge = 5
 * )
 *
 * @package Drupal\dipas\Plugin\CockpitDataResponse
 */
class CockpitDataMap extends CockpitDataResponseBase {

  use DateTimeTrait;
  use TaxonomyTermFunctionsTrait;
  use DomainAwareTrait;

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
   * Drupals taxonomy term storage service.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $termStorage;

  /**
   * Drupal's config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function setAdditionalDependencies(ContainerInterface $container) {
    $this->configFactory = $container->get('config.factory');
    $this->dateFormatter = $container->get('date.formatter');
    $this->nodeStorage = $this->entityTypeManager->getStorage('node');
    $this->termStorage = $this->entityTypeManager->getStorage('taxonomy_term');
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
  public function getPluginResponse() {
    $startDates = [];
    $features = [];

    foreach ($this->configFactory->listAll('dipas.') as $dipas_config_identifier) {
      [,$domain_id,] = explode('.', $dipas_config_identifier);

      // skip default-Domain
      if ($this->isDomainModuleInstalled() && $domain_id === 'default') {
        continue;
      }

      // Skip non-existent proceedings (former proceedings, to which the domain
      // configuration was deleted as well as inactive domain entries)
      if (
        ($domain_config = $this->configFactory->get(sprintf('domain.record.%s', $domain_id))) &&
        !(
          !$domain_config->isNew() &&
          $domain_config->get('status')
        )
      ) {
        continue;
      }

      $dipasConfigDomain = $this->configFactory->get($dipas_config_identifier);

      // Skip unpublished proceedings
      if ($dipasConfigDomain->get('Export.proceeding_is_internal')) {
        continue;
      }

      // Skip proceedings without centerpoint
      if (!$project_area_centerpoint = $dipasConfigDomain->get('ProjectArea.project_area_centerpoint')) {
        continue;
      }

      // Skip unstarted proceedings
      if (time() < strtotime($dipasConfigDomain->get('ProjectSchedule.project_start'))) {
        continue;
      }
      $dateStart = $this->convertTimestampToUTCDateTimeString(
        strtotime($dipasConfigDomain->get('ProjectSchedule.project_start')),
        TRUE
      );
      $status = $this->getStatus($dipasConfigDomain);

      if ($status === 'aktiv') {
        $statusIcon = Url::fromUri(
          'base:/' . drupal_get_path('module', 'dipas') .'/assets/',
          ['absolute' => TRUE]
         )->toString() . 'icon_active_gfi.svg';
      }
      elseif ($status === 'inaktiv') {
        $statusIcon = Url::fromUri(
          'base:/' . drupal_get_path('module', 'dipas') .'/assets/',
          ['absolute' => TRUE]
         )->toString() . 'icon_inactive_gfi.svg';
      }
      else {
        $statusIcon = Url::fromUri(
          'base:/' . drupal_get_path('module', 'dipas') .'/assets/',
          ['absolute' => TRUE]
         )->toString() . 'icon_latest_gfi.svg';
      }

      // Create a feature container for the current project.
      $featureObject = new GeoJSONFeature();

      $coordinates = array_map('floatval', explode(', ', $project_area_centerpoint));
      $featureObject->addPoint($coordinates);
      $featureObject->setGeometryType('Point');

      // Add gfi information to it.
      $featureObject->addProperty('id', $domain_id);
      $featureObject->addProperty('proceeding', $dipasConfigDomain->get('ProjectInformation.site_name'));
      $featureObject->addProperty('description', $dipasConfigDomain->get('ProjectInformation.text'));
      $featureObject->addProperty('districts', join('; ', $this->getAssignedTerms('districts', [], $dipasConfigDomain->get('ProjectInformation.data_districtselection'), 'name')));
      $featureObject->addProperty('themes', join('; ', $this->getAssignedTerms('topics', [], $dipasConfigDomain->get('ProjectInformation.data_topicselection'), 'name')));
      $featureObject->addProperty('status', $status);
      $featureObject->addProperty('status_icon', $statusIcon);
      $featureObject->addProperty('dateStart', $dateStart);
      $featureObject->addProperty('dateEnd', $this->convertTimestampToUTCDateTimeString(
        strtotime($dipasConfigDomain->get('ProjectSchedule.project_end')),
        TRUE
      ));
      $featureObject->addProperty('responsible', join('; ', $this->getAssignedTerms('project_owner', [], $dipasConfigDomain->get('ProjectInformation.project_owners'), 'name')));
      $featureObject->addProperty('numberContributions', $this->getNodeCount($domain_id, 'contribution'));
      $featureObject->addProperty('numberComments', $this->getCommentCount($domain_id));
      $featureObject->addProperty('documentation', $this->getDokumentationDetails($domain_id));
      $featureObject->addProperty('link', preg_replace('/drupal\/.*$/', $domain_id . '/#', Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString()));

      $features[] = $featureObject->getFeature();
    }

    //define styling
    $styles = (object) [
      'styleId' => 'projectAreaStyles',
      'rules' => [
        (object) [
          'conditions' => (object) [
            'properties' => (object) [
              'status' => 'aktiv',
            ],
          ],
          'style' => (object) [
            'type' => 'icon',
            'imagePath' => Url::fromUri(
              'base:/' . drupal_get_path('module', 'dipas') .'/assets/',
              ['absolute' => TRUE]
             )->toString(),
            'imageName' => 'icon_active_map.svg',
            'imageScale'=> 1.8,
            'imageWidth' => 24,
            'imageHeight' => 24,
            'imageOffsetX' => 0.5,
            'imageOffsetY'=> 0.5,
          ],
        ],
        (object) [
          'conditions' => (object) [
            'properties' => (object) [
              'status' => 'inaktiv',
            ],
          ],
          'style' => (object) [
            'type' => 'icon',
            'imagePath' => Url::fromUri(
              'base:/' . drupal_get_path('module', 'dipas') .'/assets/',
              ['absolute' => TRUE]
             )->toString(),
             'imageName' => 'icon_inactive_map.svg',
            'imageScale'=> 1.7,
            'imageWidth' => 24,
            'imageHeight' => 24,
            'imageOffsetX' => 0.5,
            'imageOffsetY'=> 0.5,
          ],
        ],
        (object) [
          'conditions' => (object) [
            'properties' => (object) [
              'status' => 'neu',
            ],
          ],
          'style' => (object) [
            'type' => 'icon',
            'imagePath' => Url::fromUri(
              'base:/' . drupal_get_path('module', 'dipas') .'/assets/',
              ['absolute' => TRUE]
             )->toString(),
            'imageName' => 'icon_latest_map.svg',
            'imageScale'=> 1.8,
            'imageWidth' => 24,
            'imageHeight' => 24,
            'imageOffsetX' => 0.5,
            'imageOffsetY'=> 0.5,
          ],
        ]
      ],
    ];

    // construct final geojson object
    $geojson['type'] = 'FeatureCollection';
    $geojson['features'] = $features;
    $geojson['styles'][] = $styles;

    return $geojson;
  }

  /**
   * Returns a string indicating if the proceeding is active or inactive.
   *
   * @param object $dipasConfigDomain
   *   The config of the coresponding domain.
   *
   * @return String
   */
  protected function getStatus($dipasConfigDomain) {
    $startDate = strtotime($dipasConfigDomain->get('ProjectSchedule.project_start'));
    $endDate = strtotime($dipasConfigDomain->get('ProjectSchedule.project_end'));
    $now = time();

    if (strtotime($dipasConfigDomain->get('ProjectSchedule.project_start')) > strtotime('-12 days')) {
      return 'neu';
    }
    if ($now >= $startDate && $now < $endDate) {
      return 'aktiv';
    }
    else {
      return 'inaktiv';
    }
  }

  /**
   * Returns the number of nodes related to the project.
   *
   * @param string $domain_id
   *   The id of the selected domain.
   * @return Number
   */
  protected function getNodeCount($domain_id, $nodeType) {
    $node_query = $this->nodeStorage->getQuery()
                  ->condition('type', $nodeType, '=')
                  ->condition('status', 1, '=')
                  ->condition('field_domain_access', $domain_id, '=')
                  ->count();

    $nodeCount = $node_query->execute();

    return $nodeCount;
  }

  /**
   * Returns the number of comments to the project.
   *
   * @param string $domain_id
   *   The id of the selected domain.
   * @return Number
   */
  protected function getCommentCount($domain_id) {
    $commentCount = \Drupal::entityQuery('comment')
      ->condition('status', 1, '=')
      ->condition('field_domain_access', $domain_id, '=')
      ->count()
      ->execute();

    return $commentCount;
  }

  /**
   * Returns a list of all documentation of a project.
   *
   * List the name, the url and the icon to be used in the masterportal gfi.
   *
   * @return array
   *   array of all media entities of type 'download' and flag for documentation
   */
  protected function getDokumentationDetails($domain_id) {
    $entityQuery = $this->entityTypeManager->getStorage('media')->getQuery()
      ->condition('bundle', 'download', '=')
      ->condition('field_domain_access', $domain_id, '=')
      ->condition('field_serve_for_documentation', TRUE, '=');

    $media_entity_id_list = $entityQuery->execute();

    $documents = [];

    foreach ($media_entity_id_list as $media_entity_id => $value) {
      $media_entity = $this->entityTypeManager->getStorage('media')
        ->load($media_entity_id);

      $document_fid = $media_entity->get('field_media_file')
        ->first()
        ->get('target_id')
        ->getString();

      $document_name = $media_entity->getName();
      $document = $this->entityTypeManager->getStorage('file')->load($document_fid);

      $document_data = [
        'name' => $document_name,
        'url' => file_create_url($document->getFileUri()),
        'icon' => Url::fromUri(
          'base:/' . drupal_get_path('module', 'dipas') .'/assets/',
          ['absolute' => TRUE]
         )->toString() . 'picture_as_pdf_white_24dp.svg'
      ];

      $documents[] = $document_data;
    }

    return $documents;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityTypeManager() {
    return $this->entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  protected function getResponseKeyCacheTags() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  protected function getTermStorage() {
    return $this->termStorage;
  }

}

