<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\CockpitDataResponse;

use stdClass;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;

/**
 * Class CockpitDataLineChart.
 *
 * @CockpitDataResponse(
 *   id = "cockpitdatalinechart",
 *   description = @Translation("Returns proceeding statistics data for line charts on participation cockpit."),
 *   requestMethods = {
 *     "GET",
 *   },
 *   isCacheable = true,
 *   maxAge = 5
 * )
 *
 * @package Drupal\dipas\Plugin\CockpitDataResponse
 */
class CockpitDataLineChart extends CockpitDataResponseBase {

  /**
   * {@inheritdoc}
   */
  public function getPluginResponse() {
    $data = $this->getDataFromDatabase();

    return $data;
  }

  /**
   * {@inheritdoc}
   */
  protected function getResponseKeyCacheTags() {
    return [];
  }

  protected function getDataFromDatabase() {
    $query = $this->database->select('dipas_cockpit_data', 'dcd')->fields('dcd');
    $result = $query->execute();
    // Run through the result set and save it in an array
    foreach($result as $row) {
      $record['contribution'][] = $row->contributions;
      $record['comments'][] = $row->comments;
      $record['year'][] = $row->year;
      $record['month'][] = $row->month;
    }
    $record['title'] = 'Beitrags- und Kommentarzahlen';
    return $record;
  }
}

