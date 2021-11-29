<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\CockpitDataResponse;

use stdClass;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CockpitDataMap.
 *
 * @CockpitDataResponse(
 *   id = "cockpitdatamap",
 *   description = @Translation("Returns proceeding statistics data for map showing proceeding areas on participation cockpit."),
 *   requestMethods = {
 *     "GET",
 *   },
 *   isCacheable = true,
 *   maxAge = 5
 * )
 *
 * @package Drupal\dipas\Plugin\CockpitDataResponse
 */
class CockpitDataMap extends CockpitDataResponseBase {

  /**
   * {@inheritdoc}
   */
  public function getPluginResponse() {
    
    return [
        'dummy1' => 'test1',
        'dummy2' => 'test2',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getResponseKeyCacheTags() {
    return [];
  }

}

