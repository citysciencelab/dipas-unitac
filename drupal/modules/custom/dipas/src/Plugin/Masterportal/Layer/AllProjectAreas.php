<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\Masterportal\Layer;

use DateTime;
use Drupal\Core\Url;
use Drupal\masterportal\GeoJSONFeature;
use Drupal\masterportal\PluginSystem\LayerPluginInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Implements a GeoJson layer of all dipas project areas for the Masterportal.
 *
 * @Layer(
 *   id = "allprojectareas",
 *   title = @Translation("Project areas of all DIPAS projects.")
 * )
 *
 * @package Drupal\dipas\Plugin\Masterportal\Layer
 */
class AllProjectAreas implements LayerPluginInterface {

  /**
   * The currently processed request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * Array of cache tags for the layer data.
   *
   * @var array
   */
  
  protected $cacheTags;

  /**
   * @var bool
   */
  protected $domainModulePresent;
 
  /**
   * {@inheritdoc}
   */
  public function __construct(Request $current_request) {}

  /**
   * {@inheritdoc}
   */
  public function getLayerDefinition() {
    return (object) [
      'version' => 1,
      'styleId' => 'allprojectareasstyle',
      'gfiAttributes' => (object) [
        'proceeding' => 'Verfahren',
        'themes' => 'Themen',
        'status' => 'Status',
        'timeperiond' => 'Zeitraum',
        'responsible' => 'Zuständig',
        'link' => 'Link',
        
      ]
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getGeoJSONFeatures() {
    $config_factory = \Drupal::configFactory();

    /* @var \Drupal\Core\DependencyInjection\Container $container */
    $container = \Drupal::getContainer();

    $this->moduleHandler = $container->get('module_handler');

    if ($this->moduleHandler->moduleExists('domain')) {
      $this->domainModulePresent = TRUE;
    }

    foreach ($config_factory->listAll('dipas.') as $dipas_config_identifier) {

      // skip default-Domain
      if ($this->domainModulePresent && $dipas_config_identifier === 'dipas.default.configuration') {
        continue;
      }

      $domain_id = preg_replace('/dipas\.(.+?)\.configuration/', '$1', $dipas_config_identifier);
      if (
        ($domain_config = $config_factory->get(sprintf('domain.record.%s', $domain_id))) &&
        !(
          !$domain_config->isNew() &&
          $domain_config->get('status')
        )
      ) {
        continue;
      }

      $dipasConfigDomain = $config_factory->get($dipas_config_identifier);
      
      if (!$project_area = json_decode($dipasConfigDomain->get('ProjectArea.project_area'))) { continue;};

      // Create a feature container for the current project.
      $featureObject = new GeoJSONFeature();

      // Add the geolocation information to it.
      if (!empty($project_area->geometry)) {
        $featureObject->setGeometry($project_area->geometry);
      }
      elseif ($project_area) {
        $featureObject->setGeometry($project_area);
      }

      // Add gfi information to it.
      $featureObject->addProperty('id', $domain_id);
      $featureObject->addProperty('proceeding', $dipasConfigDomain->get('ProjectInformation.site_name'));
      $featureObject->addProperty('themes', $this->getTermList($domain_id, 'categories'));
      $featureObject->addProperty('status', $this->getStatus($dipasConfigDomain));
      $featureObject->addProperty('timeperiod', $this->getProceedingTimeSpan($dipasConfigDomain));
      $featureObject->addProperty('responsible', $dipasConfigDomain->get('ProjectInformation.department'));
      $replace_string = "://$domain_id.";
      $featureObject->addProperty('link', preg_replace('/:\/\//', $replace_string, preg_replace('/\/drupal\/.*$/', '/#', Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString())));

      $features[] = $featureObject;
    }
    return $features;
  }

  /**
   * Returns a string of all terms contained in a vocabulary.
   *
   * @param string $domain_id
   *   The id of the selected domain.
   *
   * @param string $vocab
   *   The name of the vocabulary.
   *
   * @return String|false
   */
  protected function getTermList($domain_id, $vocab) {
    $serviceContainer = \Drupal::getContainer();
    /* @var \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager */
    $entityTypeManager = $serviceContainer->get('entity_type.manager');

    // Load all terms from the given vocabulary.
    /* @var \Drupal\taxonomy\TermInterface[] $terms */
    $terms = $entityTypeManager->getStorage('taxonomy_term')->loadByProperties([
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

    $listString = '';
    foreach($terms as $term) {
      $listString != "" && $listString .= ", ";
      $listString .= $term->label();
    }

    return $listString;
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

    if ($now >= $startDate && $now < $endDate) { 
      return 'aktiv';
    }
    else {
      return 'inaktiv';
    }
  }

  /**
   * Returns a string indcluding the set time period in the dipas domain config.
   *
   * @param object $dipasConfigDomain
   *   The config of the coresponding domain.
   *
   * @return String
   */
  protected function getProceedingTimeSpan($dipasConfigDomain) {
    $startDate = strtotime($dipasConfigDomain->get('ProjectSchedule.project_start'));
    $endDate = strtotime($dipasConfigDomain->get('ProjectSchedule.project_end'));
    $startDateObject = new DateTime($dipasConfigDomain->get('ProjectSchedule.project_start'));
    $endDateObject = new DateTime($dipasConfigDomain->get('ProjectSchedule.project_end'));
    $durationDays = $startDateObject->diff($endDateObject)->format("%a");

    return date("d.m.Y", $startDate) . ' bis ' . date("d.m.Y", $endDate) . ' (' . $durationDays . ' Tage)';

  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return ['Layer:Projectarea'];
  }

}
