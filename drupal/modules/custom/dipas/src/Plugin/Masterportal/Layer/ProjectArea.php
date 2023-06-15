<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\Masterportal\Layer;

use Drupal\masterportal\GeoJSONFeature;
use Drupal\masterportal\PluginSystem\LayerPluginInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Implements a layer plugin for the Masterportal.
 *
 * @Layer(
 *   id = "projectarea",
 *   title = @Translation("Project area of the DIPAS project as a GeoJSON layer.")
 * )
 *
 * @package Drupal\dipas\Plugin\Masterportal\Layer
 */
class ProjectArea implements LayerPluginInterface {

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
      'styleId' => 'projectareastyle',
      'gfiAttributes' => 'ignore',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getGeoJSONFeatures() {
    /* @var \Drupal\dipas\Controller\DipasConfig $dipasConfig */
    $dipasConfig = \Drupal::service('dipas.config');

    $projectarea = json_decode($dipasConfig->get('ProjectArea.project_area'));
    $features = [];

    if (!empty($projectarea)) {
      $feature = new GeoJSONFeature();
      $feature->setGeometry($projectarea);
      $feature->addProperty('Thema', 'DIPAS project area');
      $features[] = $feature;
    }

    return $features;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return ['Layer:Projectarea'];
  }

}
