<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\CockpitDataResponse;

use stdClass;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CockpitDataNumbers.
 *
 * @CockpitDataResponse(
 *   id = "cockpitdatanumbers",
 *   description = @Translation("Returns proceeding statistical number data participation cockpit."),
 *   requestMethods = {
 *     "GET",
 *   },
 *   isCacheable = true,
 *   maxAge = 5
 * )
 *
 * @package Drupal\dipas\Plugin\CockpitDataResponse
 */
class CockpitDataNumbers extends CockpitDataResponseBase {

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

