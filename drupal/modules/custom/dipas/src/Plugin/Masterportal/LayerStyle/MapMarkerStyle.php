<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\Masterportal\LayerStyle;

use Drupal\masterportal\Annotation\LayerStyle;
use Drupal\masterportal\PluginSystem\LayerStylePluginInterface;
use Drupal\Core\Url;

/**
 * Class MapMarkerStyle
 *
 * @LayerStyle(
 *   id = "mapmarkerstyle",
 *   title = @Translation("GeoJSON styles for the DIPAS map marker.")
 * )
 *
 * @package Drupal\dipas\Plugin\Masterportal\LayerStyle
 */
class MapMarkerStyle implements LayerStylePluginInterface {

  /**
   * @var \Drupal\Core\Extension\ExtensionPathResolver
   */
  protected $extensionPathResolver;

  /**
   * MapMarkerStyle constructor.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct() {
    $serviceContainer = \Drupal::getContainer();
    $this->extensionPathResolver = $serviceContainer->get('extension.path.resolver');
  }

  /**
   * {@inheritdoc}
   */
  public function getStyleObject() {
    return (object) [
      'styleId' => 'customMapMarkerPoint',
      'rules' => [
        (object) [
          'style' => (object) [
            'type' => 'icon',
            'imagePath' => Url::fromUri(
                'base:/' . $this->extensionPathResolver->getPath('module', 'masterportal') .'/libraries/masterportal/img/',
                ['absolute' => TRUE]
              )->toString(),
            'imageName' => 'mapMarker.svg',
            'imageScale' => 1,
            'imageWidth' => 34,
            'imageHeight' => 48,
            'imageOffsetY' => 46,
            'imageOffsetYUnit' => 'pixels',
          ],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return ['dipas:mapmarkerstyle'];
  }

}
