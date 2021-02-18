<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\PDSResponse;

use Drupal\Core\Url;
use Drupal\masterportal\GeoJSONFeature;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\dipas\Plugin\ResponseKey\ProjectDataTrait;
use Drupal\dipas\Plugin\ResponseKey\NodeContentTrait;
use Drupal\dipas\Plugin\ResponseKey\DateTimeTrait;

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

  use NodeContentTrait{
    NodeContentTrait::getPluginResponse as protected traitPluginResponse;
  }
  use DateTimeTrait;
  use ProjectDataTrait;

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
   * {@inheritdoc}
   */
  public function setAdditionalDependencies(ContainerInterface $container) {
    $this->dateFormatter = $container->get('date.formatter');

    $this->nodeStorage = $this->entityTypeManager->getStorage('node');

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
      $domain_id = $this->domainModulePresent ? $this->currentRequest->attributes->get('proj_ID'): 'default';

      $domain_configs[] = sprintf('dipas.%s.configuration', $domain_id);
    }
    else {
      $domain_configs = $this->dipasConfig->getIds();
    }

    // Cycle over the projects.
    foreach ($domain_configs as $domain_config) {
      // skip default-Domain if Domainmodule is enabled
      if ($this->domainModulePresent && $domain_config === 'dipas.default.configuration') {
        continue;
      }

      $domain_id = preg_replace('/dipas\.(\w+)\.configuration/', '$1', $domain_config);
      $dipasConfigDomain = $this->dipasConfig->getEditable($domain_config);

      $project_area = json_decode($dipasConfigDomain->get('ProjectArea.project_area'));

      // Find projectinformation description ToDo!!

      // Create a feature container for the current project.
      $featureObject = new GeoJSONFeature();

      // Add the geolocation information to it.
      if (!empty($project_area->geometry)) {
        $featureObject->setGeometry($project_area->geometry);
      }
      else {
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
      $featureObject->addProperty('publisher', $dipasConfigDomain->get('ProjectInformation.data_responsible'));
      $featureObject->addProperty('standardCategories', $this->getTermList($domain_id, 'categories'));
      $featureObject->addProperty('projectContributionType', $this->getTermList($domain_id, 'rubrics'));
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

  /**
   * Returns a list of all terms contained in a vocabulary.
   *
   * @param string $domain_id
   *   The id of the selected domain.
   *
   * @param string $vocab
   *   The name of the vocabulary.
   *
   * @return array|false
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getTermList($domain_id, $vocab) {
    // Load all terms from the given vocabulary.
    /* @var \Drupal\taxonomy\TermInterface[] $terms */
    $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties([
          'vid' => $vocab,
        ]);


      /** If the domain module is present, filter any terms retrieved by
       * domain assignment.
       */

      if ($this->domainModulePresent && count($terms)) {
        $hasDomainAccessField = reset($terms)->hasField(DOMAIN_ACCESS_FIELD);
        $hasDomainAllAccessField = reset($terms)->hasField(DOMAIN_ACCESS_ALL_FIELD);
        $terms = array_filter(
          $terms,
          function (\Drupal\taxonomy\TermInterface $term) use ($hasDomainAccessField, $hasDomainAllAccessField, $domain_id) {
            if ($hasDomainAccessField) {
              $assignedDomains = array_map(
                function ($assignment) {
                  return $assignment['target_id'];
                },
                $term->get(DOMAIN_ACCESS_FIELD)->getValue()
              );
            }

            $accessOnAllDomains = $hasDomainAllAccessField
              ? (bool) $term->get(DOMAIN_ACCESS_ALL_FIELD)->getString()
              : FALSE;

            if ($accessOnAllDomains || in_array($domain_id, $assignedDomains)) {
              return TRUE;
            }

            return FALSE;
          }
        );
      }



    $list = array_map(function ($term) { return $term->label(); }, $terms);

    return $list;
  }

  protected function loadProjectDescription($dipasConfigDomain, string $project_id) {

    $project_description = '';
    $menuitem = $dipasConfigDomain->get('MenuSettings.mainmenu.projectinfo');

    if ($menuitem['enabled'] && $menuitem['node'] !== '') {
      $node = $this->entityTypeManager->getStorage('node')->load($menuitem['node']);

      $result = $this->parseEntityContent($node);

      if (array_key_exists('field_content', $result)) {

        // nur ein Feld in der Node vorhanden
        if (array_key_exists('bundle', $result['field_content'])) {
          if ($result['field_content']['bundle'] === 'text'){
            $project_description = $result['field_content']['field_text'];
          }
        }
        // mehrere Felder in der Node vorhanden
        else {
          foreach ($result['field_content'] as $definition) {
            if ($definition['bundle'] === 'text'){
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
    $nlp_cluster = $this->state->get('dipas.nlp.clustering.result:'.$domain_id);

    $list = array_map(function ($cluster) {
      return $cluster->title;
    }, $nlp_cluster['result']);

    return $list;
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

}

