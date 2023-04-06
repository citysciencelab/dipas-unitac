<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\ResponseKey;

use Drupal\dipas\Annotation\ResponseKey;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ContributionMap.
 *
 * @ResponseKey(
 *   id = "contributionmap",
 *   description = @Translation("Returns setting data for the map display of all contributions."),
 *   requestMethods = {
 *     "GET",
 *   },
 *   isCacheable = true
 * )
 *
 * @package Drupal\dipas\Plugin\ResponseKey
 */
class ContributionMap extends ResponseKeyBase {

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * @var \Drupal\dipas\Controller\DipasConfig
   */
  protected $dipasConfig;

  /**
   * {@inheritdoc}
   */
  protected function setAdditionalDependencies(ContainerInterface $container) {
    $this->database = $container->get('database');
    $this->dipasConfig = $container->get('dipas.config');
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginResponse() {

    // if a projectarea is defined, zoom the contributionmap to this area

    // get the project area from the dipas configuration
    $projectarea = json_decode($this->dipasConfig->get('ProjectArea.project_area'));

    // check if the projectarea is filled with data
    if ($projectarea && isset($projectarea->coordinates) && count($projectarea->coordinates) > 0) {
      $extent = $this->getExtentOfCoordinates($projectarea->coordinates[0]);
    }
    else {
      $extent = $this->getLonLatExtend();
    }

     // return the extent
    return [
      'extent' => $extent,
    ];

  }

  /**
   * Returns the extent coordinates of all contributions.
   *
   * @return array
   */
  protected function getLonLatExtend() {
    /* @var \Drupal\Core\Database\Query\SelectInterface $query */
    $query = $this->database->select('node__field_geodata', 'geodata')
      ->condition('geodata.bundle', 'contribution', '=')
      ->isNotNull('geodata.field_geodata_value');
    $query->addField('geodata', 'field_geodata_value', 'geodata');

    $geodata = array_map(
      function ($item) {
        return json_decode($item->geodata);
      },
      $query->execute()->fetchAll()
    );

    if (count($geodata)) {
      $extent = [
        'lon_min' => 9999999999,
        'lat_min' => 9999999999,
        'lon_max' => 0,
        'lat_max' => 0,
      ];

      foreach ($geodata as $item) {
        $coordsToCheck = $item->geometry->coordinates;
        switch ($item->geometry->type) {
          case 'Point':
            $coordsToCheck = [[$coordsToCheck]];
            break;
          case 'LineString':
            $coordsToCheck = [$coordsToCheck];
            break;
        }

        $subExtent = $this->getExtentOfCoordinates($coordsToCheck[0])  ;

        if ($subExtent['lon_min'] < $extent['lon_min']) {
          $extent['lon_min'] = $subExtent['lon_min'];
        }
        if ($subExtent['lat_min'] < $extent['lat_min']) {
          $extent['lat_min'] = $subExtent['lat_min'];
        }
        if ($subExtent['lon_max'] > $extent['lon_max']) {
          $extent['lon_max'] = $subExtent['lon_max'];
        }
        if ($subExtent['lat_max'] > $extent['lat_max']) {
          $extent['lat_max'] = $subExtent['lat_max'];
        }
      }

      $extent['lon_diff'] = $extent['lon_max'] - $extent['lon_min'];
      $extent['lon_avg'] = $extent['lon_min'] + $extent['lon_diff'] / 2;
      $extent['lat_diff'] = $extent['lat_max'] - $extent['lat_min'];
      $extent['lat_avg'] = $extent['lat_min'] + $extent['lat_diff'] / 2;

      return $extent;
    }

    return [];
  }

  /**
   * {@inheritdoc}
   */
  protected function getResponseKeyCacheTags() {
    return [];
  }

  /**
   * Calculates the extent of a single item.
   *
   * @param array $coords
   *   Array of coordinates.
   *
   * @return array
   *   The extent values of that item.
   */
  protected function getExtentOfCoordinates($coords) {
    $extent = [
      'lon_min' => 9999999999,
      'lat_min' => 9999999999,
      'lon_max' => 0,
      'lat_max' => 0,
    ];

    $latall = [];
    $lonall = [];

    // copy all longitude coordinates in one and all latitude coordinates in another array
    foreach ($coords as $coordinate) {
      $lonall[] = $coordinate[0];
      $latall[] = $coordinate[1];
    }

    // get the min and the max of both arrays
    $extent = [
      'lon_min' => min($lonall),
      'lat_min' => min($latall),
      'lon_max' => max($lonall),
      'lat_max' => max($latall),
    ];

    $extent['lon_diff'] = $extent['lon_max'] - $extent['lon_min'];
    $extent['lon_avg'] = $extent['lon_min'] + $extent['lon_diff'] / 2;
    $extent['lat_diff'] = $extent['lat_max'] - $extent['lat_min'];
    $extent['lat_avg'] = $extent['lat_min'] + $extent['lat_diff'] / 2;

    return $extent;
  }

}
