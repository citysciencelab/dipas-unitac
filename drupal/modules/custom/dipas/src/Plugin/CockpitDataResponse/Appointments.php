<?php

namespace Drupal\dipas\Plugin\CockpitDataResponse;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\dipas\Annotation\CockpitDataResponse;
use Drupal\dipas\Plugin\ResponseKey\NodeListingTrait;
use Drupal\dipas\Plugin\ResponseKey\DateTimeTrait;
use Drupal\dipas\TaxonomyTermFunctionsTrait;
use Drupal\masterportal\DomainAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Appointments.
 *
 * @CockpitDataResponse(
 *   id = "appointments",
 *   description = @Translation("Lists all upcoming appointments of all active proceedings."),
 *   requestMethods = {
 *     "GET",
 *   },
 *   isCacheable = true,
 *   maxAge = 60
 * )
 *
 * @package Drupal\dipas\Plugin\CockpitDataResponse
 */
class Appointments extends CockpitDataResponseBase {

  use NodeListingTrait;
  use DateTimeTrait;
  use TaxonomyTermFunctionsTrait;
  use DomainAwareTrait;

  /**
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Drupals configuration service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Drupal's taxonomy term storage service.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $termStorage;

  /**
   * {@inheritdoc}
   */
  public function setAdditionalDependencies(ContainerInterface $container) {
    $this->dateFormatter = $container->get('date.formatter');
    $this->configFactory = $container->get('config.factory');
    $this->termStorage = $this->entityTypeManager->getStorage('taxonomy_term');

    // Set the node listing to ignore domains at the earliest possible point in time.
    $this->listingIsDomainSensitive(FALSE);
  }

  /**
   * {@inheritdoc}
   */
  protected function getResponseKeyCacheTags() {
    $cachetags = ['CockpitDataResponse', 'CockpitDataResponseAppointments'];
    $nodes = $this->getNodes();

    foreach ($nodes as $node) {
      $cachetags[] = sprintf('node-%d', $node->nid);
    }

    return $cachetags;
  }

  /**
   * {@inheritdoc}
   */
  protected function getPluginResponse() {
    return [
      'appointments' => $this->getNodes(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function postProcessDataRow(&$row) {
    $dipasConfig = $this->getConfig($row->proceedingid);

    $row->description = strip_tags($row->description);
    $row->proceedingName = $dipasConfig->get('ProjectInformation.site_name');
    $row->proceedingtopics = $this->getAssignedTerms('topics', [], $dipasConfig->get('ProjectInformation.data_topicselection'), 'name');
    $row->proceedingdistricts = $this->getAssignedTerms('districts', [], $dipasConfig->get('ProjectInformation.data_districtselection'), 'name');
    $row->proceedingowners = $this->getAssignedTerms('project_owner', [], $dipasConfig->get('ProjectInformation.project_owners'), 'name');
  }

  /**
   * Helper function to retrieve the proceeding configuration object.
   *
   * @param string $domainid
   *   The proceeding id
   *
   * @return \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   *   The desired configuration object.
   */
  protected function getConfig($domainid) {
    $configs = drupal_static('dipas_domain_configs', []);

    if (!isset($configs[$domainid])) {
      $configs[$domainid] = $this->configFactory->get(sprintf('dipas.%s.configuration', $domainid));
    }

    return $configs[$domainid];
  }

  /**
   * {@inheritdoc}
   */
  protected function getNodeType() {
    return 'appointment';
  }

  /**
   * {@inheritdoc}
   */
  protected function getJoins() {
    return [
      [
        'type' => 'LEFT',
        'table' => 'node__field_description',
        'alias' => 'description',
        'condition' => 'base.type = description.bundle AND base.nid = description.entity_id AND base.vid = description.revision_id AND attr.langcode = description.langcode AND description.deleted = 0',
        'fields' => [
          'field_description_value' => 'description',
        ],
      ],
      [
        'type' => 'LEFT',
        'table' => 'node__field_date',
        'alias' => 'date',
        'condition' => 'base.type = date.bundle AND base.nid = date.entity_id AND base.vid = date.revision_id AND attr.langcode = date.langcode AND date.deleted = 0',
        'fields' => [
          'field_date_value' => 'start',
          'field_date_end_value' => 'end',
        ],
      ],
      [
        'type' => 'LEFT',
        'table' => 'node__field_domain_access',
        'alias' => 'domain_access',
        'condition' => 'base.type = domain_access.bundle AND base.nid = domain_access.entity_id AND base.vid = domain_access.revision_id',
        'fields' => [
          'field_domain_access_target_id' => 'proceedingid',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getExpressions() {
    return [
      'CASE WHEN COALESCE(LENGTH(date.field_date_end_value), 0) > 0 THEN date.field_date_end_value ELSE date.field_date_value END' => 'expires'
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getConditions() {
    $now = new DrupalDateTime('now');

    return [
      [
        'field' => 'date.field_date_value',
        'value' => $now->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
        'operator' => '>',
      ],
      [
        'field' => 'domain_access.field_domain_access_target_id',
        'value' => $this->getProceedingIDs('visible'),
        'operator' => 'IN',
      ]
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getGroupBy() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  protected function getSortingField() {
    return 'field_date_value';
  }

  /**
   * {@inheritdoc}
   */
  protected function getSortingDirection() {
    return 'ASC';
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
  protected function getTermStorage() {
    return $this->termStorage;
  }

}
