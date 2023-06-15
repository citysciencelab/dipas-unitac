<?php

namespace Drupal\dipas\Plugin\CockpitDataResponse;

use Drupal\dipas\Annotation\CockpitDataResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\dipas\FileHelperFunctionsTrait;
use Drupal\dipas\Plugin\ResponseKey\DateTimeTrait;
use Drupal\dipas\TaxonomyTermFunctionsTrait;

/**
 * Class CockpitDataDocumentationDocuments.
 *
 * @CockpitDataResponse(
 *   id = "documentations",
 *   description = @Translation("Returns proceeding documents marked for documentation participation cockpit."),
 *   requestMethods = {
 *     "GET",
 *   },
 *   isCacheable = true,
 *   maxAge = 5
 * )
 *
 * @package Drupal\dipas\Plugin\CockpitDataResponse
 */
class Documentations extends CockpitDataResponseBase {

  use FileHelperFunctionsTrait;
  use TaxonomyTermFunctionsTrait;
  use DateTimeTrait;

  /**
   * Drupals date formatter.
   *
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
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * {@inheritdoc}
   */
  public function setAdditionalDependencies(ContainerInterface $container) {
    $this->dateFormatter = $container->get('date.formatter');
    $this->configFactory = $container->get('config.factory');
    $this->termStorage = $this->entityTypeManager->getStorage('taxonomy_term');
    $this->fileUrlGenerator = $container->get('file_url_generator');

    // Set the node listing to ignore domains at the earliest possible point in
    // time.
    $this->listingIsDomainSensitive(FALSE);
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginResponse() {
    return [
      'files' => $this->getDownloadPathFromEntities([
        [
          'field' => 'field_serve_for_documentation',
          'value' => 1,
          'operator' => '=',
        ]
      ]),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function postProcessDataRow(&$row) {
    $dipasConfig = $this->getConfig($row['proceedingid']);

    $row['proceedingtopics'] = $this->getAssignedTerms('topics', [], $dipasConfig->get('ProjectInformation.data_topicselection'), 'name');
    $row['proceedingdistricts'] = $this->getAssignedTerms('districts', [], $dipasConfig->get('ProjectInformation.data_districtselection'), 'name');
    $row['proceedingowners'] = $this->getAssignedTerms('project_owner', [], $dipasConfig->get('ProjectInformation.project_owners'), 'name');
    $row['proceedingschedule_start'] = $dipasConfig->get('ProjectSchedule.project_start');
    $row['proceedingschedule_end'] = $dipasConfig->get('ProjectSchedule.project_end');
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
  protected function getDateFormatter() {
    return $this->dateFormatter;
  }

  /**
   * {@inheritdoc}
   */
  protected function getTermStorage() {
    return $this->termStorage;
  }

  /**
   * {@inheritdoc}
   */
  protected function getFileUrlGenerator() {
    return $this->fileUrlGenerator;
  }

}
