<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html
 *   GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\Masterportal\Layer;

use Drupal\masterportal\DomainAwareTrait;
use Drupal\masterportal\GeoJSONFeature;
use Drupal\masterportal\PluginSystem\LayerPluginInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Request;


/**
 * Implements a layer plugin for the Masterportal.
 *
 * @Layer(
 *   id = "contributions",
 *   title = @Translation("DIPAS contributions")
 * )
 *
 * @package Drupal\dipas\Plugin\Masterportal\Layer
 */
class Contributions implements LayerPluginInterface {

  use DomainAwareTrait;

  /**
   * The currently processed request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

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
   * Array of cache tags for the layer data.
   *
   * @var array
   */
  protected $cacheTags;

  /**
   * {@inheritdoc}
   */
  public function __construct(Request $current_request) {
    /* @var \Drupal\Core\DependencyInjection\ContainerInjectionInterface $serviceContainer */
    $serviceContainer = \Drupal::getContainer();
    /* @var \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager */
    $entityTypeManager = $serviceContainer->get('entity_type.manager');
    $this->nodeStorage = $entityTypeManager->getStorage('node');
    $this->termStorage = $entityTypeManager->getStorage('taxonomy_term');
    $this->currentRequest = $current_request;
    $this->cacheTags = drupal_static('DipasContributionStreamCacheTags', ['Layer:ContributionGeoJSON']);
  }

  /**
   * {@inheritdoc}
   */
  public function getLayerDefinition() {
    return (object) [
      'version' => 1,
      'styleId' => 'contributions',
      'minScale' => '0',
      'maxScale' => '2500000',
      'gfiAttributes' => (object) [
        'Thema' => 'Kategorie',
        'Rubric' => 'Typ',
        'description' => 'description',
        'name' => 'name',
        'link' => 'link',
        'nid' => 'nid',
      ],
      'gfiTheme' => (object) [
        'name' => 'dipas',
        'params' => (object) [
          'gfiIconPath' => Url::fromUri(
            'base:/' . drupal_get_path('module', 'dipas') .'/assets/09_grau.png',
            ['absolute' => TRUE]
          )->toString(),
        ] 
      ],
      'legend' => TRUE,
      'layerAttribution' => 'nicht vorhanden',
      'cache' => FALSE,
      'datasets' => [],
      'autoRefresh' => 60000,
      'hitTolerance' => 10,
      'searchField' => [
        'name',
        'description',
      ],
      'filterOptions' => [
        (object) [
          'fieldName' => 'Thema',
          'filterType' => 'combo',
          'filterName' => 'Kategorie',
          'filterString' => [
            '*',
          ],
        ],
        (object) [
          'fieldName' => 'Rubric',
          'filterType' => 'combo',
          'filterName' => 'Typ',
          'filterString' => [
            '*',
          ],
        ],
      ],
      'extendedFilter' => TRUE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getGeoJSONFeatures() {
    $features = [];

    $query = $this->nodeStorage->getQuery()
      ->condition('type', 'contribution', '=')
      ->condition('status', 1, '=');

    if ($this->isDomainModuleInstalled()) {
      $query->condition('field_domain_access', $this->getActiveDomain(), '=');
    }

    if (
      $this->currentRequest->query->has('hideOthers') &&
      $this->currentRequest->query->has('node') &&
      is_numeric($this->currentRequest->query->get('node'))
    ) {
      $query->condition('nid', $this->currentRequest->query->get('node'), '=');
    }

    if ($this->currentRequest->query->has('category')) {
      $categories = array_flip(array_map(
        function (TermInterface $term) {
          return strtolower($term->getName());
        },
        $this->termStorage->loadByProperties(['vid' => 'categories'])
      ));
      if (!empty($category = $categories[strtolower($this->currentRequest->query->get('category'))])) {
        $query->condition('field_category', $category, '=');
      }
    }

    // Load all published contributions.
    $nodeIds = $query->execute();
    $contributionNodes = $this->nodeStorage->loadMultiple($nodeIds);

    // Enrich the cache tags array.
    $this->cacheTags = array_merge(
      $this->cacheTags,
      array_map(
        function ($item) {
          return sprintf('node:%d', $item);
        },
        $nodeIds
      )
    );

    // Cycle over the contributions.
    foreach ($contributionNodes as $node) {

      // Get the location of the current node.
      $geodata = ($fieldValue = $node->get('field_geodata')
        ->first()) ? $fieldValue->getString() : '';

      // Exclude non-localized contributions from the GeoJSON feed.
      if (empty($geodata) || ($geodata = json_decode($geodata)) === NULL || count((array) $geodata) === 0) {
        continue;
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

      // Create a feature container for the current contribution.
      $featureObject = new GeoJSONFeature();

      // Add the geolocation information to it.
      if ($castToPoint = $this->currentRequest->query->has('castToPoint') && !empty($geodata->centerPoint)) {
        if (count((array) $geodata->centerPoint)){
          $featureObject->setGeometry($geodata->centerPoint);
        }
        else {
          // Exclude contributions with broken localization from the GeoJSON feed, if castToPoint is set. Seen in Version 0.5.3 (probably also in 1.0.0-rc)
          continue;
        }
      }
      else {
        if (!$castToPoint && !empty($geodata->geometry)) {
          $featureObject->setGeometry($geodata->geometry);
        }
        else {
          $featureObject->setGeometry($geodata);
        }
      }

      // Add the node information to it.
      $featureObject->addProperty('nid', $node->id());
      $featureObject->addProperty('name', $node->label());
      $featureObject->addProperty('description', $node->get('field_text')
        ->first()
        ->getString());
      $featureObject->addProperty('link', $node->toUrl()->toString());
      $featureObject->addProperty('Thema', $taxonomyString['category']);
      $featureObject->addProperty('Rubric', $taxonomyString['rubric']);

      // Add the current node to the content container.
      $features[] = $featureObject;
    }

    return $features;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return $this->cacheTags;
  }

}
