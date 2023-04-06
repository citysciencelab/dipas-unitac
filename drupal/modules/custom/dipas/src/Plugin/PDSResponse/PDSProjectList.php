<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\PDSResponse;

use Drupal\Core\Url;
use Drupal\dipas\Annotation\PDSResponse;
use Drupal\masterportal\GeoJSONFeature;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\dipas\Plugin\ResponseKey\ProjectDataTrait;
use Drupal\dipas\Plugin\ResponseKey\NodeContentTrait;
use Drupal\dipas\Plugin\ResponseKey\DateTimeTrait;
use Drupal\dipas\TaxonomyTermFunctionsTrait;
use Drupal\masterportal\DomainAwareTrait;

/**
 * Class PDSProjectList.
 *
 * @PDSResponse(
 *   id = "pdsprojectlist",
 *   description = @Translation("Returns a list of projects currently contained in the database following the PDS standard."),
 *   requestMethods = {
 *     "GET",
 *   },
 *   isCacheable = true
 * )
 *
 * @package Drupal\dipas\Plugin\PDSResponse
 */
class PDSProjectList extends PDSResponseBase {

  use NodeContentTrait {
    NodeContentTrait::getPluginResponse as protected traitPluginResponse;
  }
  use DateTimeTrait;
  use ProjectDataTrait;
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
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * {@inheritdoc}
   */
  public function setAdditionalDependencies(ContainerInterface $container) {
    $this->dateFormatter = $container->get('date.formatter');
    $this->nodeStorage = $this->entityTypeManager->getStorage('node');
    $this->termStorage = $this->entityTypeManager->getStorage('taxonomy_term');
    $this->fileUrlGenerator = $container->get('file_url_generator');
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
  protected function getDipasConfig() {
    return $this->dipasConfig;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginResponse() {
    $features = [];
    $domain_configs = [];
    if ($this->currentRequest->attributes->get('proj_ID') !== '0') {
      // return data of a single project
      $domain_id = $this->domainModulePresent ? $this->currentRequest->attributes->get('proj_ID') : 'default';

      $domain_configs[] = sprintf('dipas.%s.configuration', $domain_id);
    } else {
      $domain_configs = $this->dipasConfig->getIds();
    }

    // Cycle over the projects.
    foreach ($domain_configs as $domain_config) {
      // skip default-Domain if Domainmodule is enabled
      if ($this->domainModulePresent && $domain_config === 'dipas.default.configuration') {
        continue;
      }

      $domain_id = preg_replace('/dipas\.(.+?)\.configuration/', '$1', $domain_config);
      $dipasConfigDomain = $this->dipasConfig->getEditable($domain_config);

      if ($dipasConfigDomain->get('Export.proceeding_is_internal')) {
        continue;
      }

      $project_area = json_decode($dipasConfigDomain->get('ProjectArea.project_area'));

      // Find projectinformation description ToDo!!

      // Create a feature container for the current project.
      $featureObject = new GeoJSONFeature();

      // Add the geolocation information to it.
      if (!empty($project_area->geometry)) {
        $featureObject->setGeometry($project_area->geometry);
      } elseif ($project_area) {
        $featureObject->setGeometry($project_area);
      }
      // Add the node information to it.
      $featureObject->addProperty('id', $domain_id);
      $featureObject->addProperty('nameFull', $dipasConfigDomain->get('ProjectInformation.site_name'));
      $featureObject->addProperty('description', $this->loadProjectDescription($dipasConfigDomain, ''));
      $featureObject->addProperty('dateStart', $this->convertTimestampToUTCDateTimeString(
        strtotime($dipasConfigDomain->get('ProjectSchedule.project_start')),
        TRUE
      ));
      $featureObject->addProperty('dateEnd', $this->convertTimestampToUTCDateTimeString(
        strtotime($dipasConfigDomain->get('ProjectSchedule.project_end')),
        TRUE
      ));
      $featureObject->addProperty('dipasPhase', $this->getDipasPhase($dipasConfigDomain));
      $replace_string = "://$domain_id.";
      $featureObject->addProperty('website', preg_replace('/:\/\//', $replace_string, preg_replace('/\/drupal\/.*$/', '/#', Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString())));
      $featureObject->addProperty('owner', $dipasConfigDomain->get('ProjectInformation.department'));
      $featureObject->addProperty('projectOwner', $this->getAssignedTerms('project_owner', [], $dipasConfigDomain->get('ProjectInformation.project_owners')));
      $featureObject->addProperty('publisher', $dipasConfigDomain->get('ProjectInformation.data_responsible'));

      ($domain_id === 'default') ?
        $featureObject->addProperty('standardCategories', $this->getTermList('categories')) :
        $featureObject->addProperty('standardCategories', $this->getTermList('categories', [], FALSE, $domain_id));

        ($domain_id === 'default') ?
        $featureObject->addProperty('projectContributionType', $this->getTermList('rubrics')) :
        $featureObject->addProperty('projectContributionType', $this->getTermList('rubrics', [], FALSE, $domain_id));

      $featureObject->addProperty('dipasMainDistrict', $this->getAssignedTerms('districts', ['field_color' => function ($fieldvalue) {
        return $fieldvalue->getString();
      }], $dipasConfigDomain->get('ProjectInformation.data_districtselection')));
      $featureObject->addProperty('projectTopics', $this->getAssignedTerms('topics', [], $dipasConfigDomain->get('ProjectInformation.data_topicselection')));
      $featureObject->addProperty('referenceSystem', "4326");
      $featureObject->addProperty('hasParticipatoryText', $this->getNodeList($domain_id));
      $featureObject->addProperty('dipasCategoriesCluster', $this->getClusterList($domain_id));

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

  protected function loadProjectDescription($dipasConfigDomain, string $project_id) {
    $project_description = '';
    $menuitem = $dipasConfigDomain->get('MenuSettings.mainmenu.projectinfo');

    if ($menuitem && $menuitem['enabled'] && $menuitem['node'] !== '') {
      $node = $this->entityTypeManager->getStorage('node')->load($menuitem['node']);

      $result = $this->parseEntityContent($node);

      if (array_key_exists('field_content', $result)) {

        // nur ein Feld in der Node vorhanden
        if (array_key_exists('bundle', $result['field_content'])) {
          if ($result['field_content']['bundle'] === 'text') {
            $project_description = $result['field_content']['field_text'];
          }
        }
        // mehrere Felder in der Node vorhanden
        else {
          foreach ($result['field_content'] as $definition) {
            if ($definition['bundle'] === 'text') {
              $project_description = $project_description . ' ' . $definition['field_text'];
            }
          }
        }
      }
    }

    return strip_tags($project_description);
  }

  /**
   * Returns a list of all nodes related to the project.
   *
   * @param string $domain_id
   *   The id of the selected domain.
   *
   *
   * @return array|false
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getNodeList($domain_id) {
    $node_query = $this->nodeStorage->getQuery()
      ->condition('type', 'contribution', '=')
      ->condition('status', 1, '=')
      ->condition('field_domain_access', $domain_id, '=');
    // Load all published contributions.
    $nodeIds = $node_query->execute();

    $list = array_values($nodeIds);

    return $list;
  }

  /**
   * Returns a list of all nlp clusters related to the project.
   *
   * @param string $domain_id
   *   The id of the selected domain.
   *
   *
   * @return array|false
   */
  protected function getClusterList($domain_id) {
    $nlp_cluster = $this->state->get('dipas.nlp.clustering.result:' . $domain_id);

    if ($nlp_cluster && $nlp_cluster['result']) {
      $list = array_map(function ($cluster) {
        return $cluster->title;
      }, $nlp_cluster['result']);

      return $list;
    }
    return null;
  }

  /**
   * Returns the current phase of the project or the latest phase before it was switched to frozen state.
   *
   * @param object $dipasConfigDomain
   *   The object of the domain specific configuration.
   *
   *
   * @return string
   */
  protected function getDipasPhase($dipasConfigDomain) {

    $project_phase = $this->getProjectPhase();

    if ($project_phase === 'frozen') {
      // if it is frozen get last active phase before it was switched to frozen
      $project_phase = 'phase1';

      if ($dipasConfigDomain->get('ProjectSchedule.phase_2_enabled')) {
        $project_phase = 'phase2';
      }

      if ($dipasConfigDomain->get('ProjectSchedule.phasemix_enabled')) {
        $project_phase = 'phasemix';
      }
    }

    return $project_phase;
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
