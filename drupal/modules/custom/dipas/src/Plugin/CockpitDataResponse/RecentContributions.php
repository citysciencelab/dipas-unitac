<?php

namespace Drupal\dipas\Plugin\CockpitDataResponse;

use Drupal\dipas\Annotation\CockpitDataResponse;
use Drupal\dipas\Plugin\ResponseKey\NodeListingTrait;
use Drupal\dipas\Plugin\ResponseKey\DateTimeTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\dipas\TaxonomyTermFunctionsTrait;

/**
 * Class RecentContributions.
 *
 * @CockpitDataResponse(
 *   id = "recentcontributions",
 *   description = @Translation("Lists the latest three contributions accross all visible domains."),
 *   requestMethods = {
 *     "GET",
 *   },
 *   isCacheable = true,
 *   maxAge = 15
 * )
 *
 * @package Drupal\dipas\Plugin\CockpitDataResponse
 */
class RecentContributions extends CockpitDataResponseBase {

  use NodeListingTrait;
  use DateTimeTrait;
  use TaxonomyTermFunctionsTrait;

  /**
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $termStorage;

  /**
   * {@inheritdoc}
   */
  protected function setAdditionalDependencies(ContainerInterface $container) {
    $this->dateFormatter = $container->get('date.formatter');
    $this->configFactory = $container->get('config.factory');
    $this->termStorage = $this->entityTypeManager->getStorage('taxonomy_term');

    // Set listing to not be domain sensitive as early as possible.
    $this->listingIsDomainSensitive(FALSE);
  }

  /**
   * {@inheritdoc}
   */
  protected function getResponseKeyCacheTags() {
    return ['CockpitDataResponse', 'CockpitDataResponseRecentContributions'] +
      array_map(function ($node) {
        return sprintf('node-%d', $node->nid);
      }, $this->getNodes());
  }

  /**
   * {@inheritdoc}
   */
  protected function getPluginResponse() {
    return [
      'contributions' => $this->getNodes(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function postProcessNodes(array &$nodes) {
    $allProceedingIDs = array_unique(array_map(function ($node) {
      return $node->domainid;
    }, $nodes));

    $proceedingConfigs = array_combine(
      $allProceedingIDs,
      array_map(function ($domainid) {
        return $this->configFactory->get(sprintf('dipas.%s.configuration', $domainid));
      }, $allProceedingIDs)
    );

    foreach ($nodes as &$node) {
      $node->content = nl2br($node->content);
      $node->proceedingName = $proceedingConfigs[$node->domainid]->get('ProjectInformation.site_name') ?: '';
      $node->proceedingDistricts = $this->getAssignedTerms('districts', [], $proceedingConfigs[$node->domainid]->get('ProjectInformation.data_districtselection'), 'name');
      $node->proceedingTopics = $this->getAssignedTerms('topics', [], $proceedingConfigs[$node->domainid]->get('ProjectInformation.data_topicselection'), 'name');
      $node->proceedingOwner = $this->getAssignedTerms('project_owner', [], $proceedingConfigs[$node->domainid]->get('ProjectInformation.project_owners'), 'name');
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getLimit() {
    return 3;
  }

  /**
   * {@inheritdoc}
   */
  protected function getNodeType() {
    return 'contribution';
  }

  /**
   * {@inheritdoc}
   */
  protected function getJoins() {
    return [
      [
        'type' => 'LEFT',
        'table' => 'node__field_domain_access',
        'alias' => 'assigned_domains',
        'condition' => 'base.type = assigned_domains.bundle AND base.nid = assigned_domains.entity_id AND base.vid = assigned_domains.revision_id AND attr.langcode = assigned_domains.langcode AND assigned_domains.deleted = 0',
        'fields' => [
          'field_domain_access_target_id' => 'domainid',
        ],
      ],
      [
        'type' => 'LEFT',
        'table' => 'node__field_domain_all_affiliates',
        'alias' => 'assigned_to_all',
        'condition' => 'base.type = assigned_to_all.bundle AND base.nid = assigned_to_all.entity_id AND base.vid = assigned_to_all.revision_id AND attr.langcode = assigned_to_all.langcode AND assigned_to_all.deleted = 0',
      ],
      [
        'type' => 'LEFT',
        'table' => 'node__field_text',
        'alias' => 'content',
        'condition' => 'base.type = content.bundle AND base.nid = content.entity_id AND base.vid = content.revision_id AND attr.langcode = content.langcode AND content.deleted = 0',
        'fields' => [
          'field_text_value' => 'content',
        ],
      ]
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getConditions() {
    $visibleDomains = $this->getProceedingIDs('visible');

    $domainConditions = $this->getOrConditionGroup();
    $domainConditions->condition(
      'assigned_domains.field_domain_access_target_id',
      $visibleDomains,
      'IN'
    );
    $domainConditions->condition(
      'assigned_to_all.field_domain_all_affiliates_value',
      1,
      '='
    );

    return [
      $domainConditions
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getExpressions() {
    return [];
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
    return 'created';
  }

  /**
   * {@inheritdoc}
   */
  protected function getSortingDirection() {
    return 'DESC';
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
  protected function getTermStorage() {
    return $this->termStorage;
  }

}
