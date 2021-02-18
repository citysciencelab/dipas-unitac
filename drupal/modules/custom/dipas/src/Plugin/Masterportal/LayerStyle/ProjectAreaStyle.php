<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\Masterportal\LayerStyle;

use Drupal\masterportal\PluginSystem\LayerStylePluginInterface;

/**
 * Class ProjectAreaStyle
 *
 * @LayerStyle(
 *   id = "projectareastyle",
 *   title = @Translation("GeoJSON layer styles for the DIPAS project area layer.")
 * )
 *
 * @package Drupal\dipas\Plugin\Masterportal\LayerStyle
 */
class ProjectAreaStyle implements LayerStylePluginInterface {

  /**
   * {@inheritdoc}
   */
  public function getStyleObject() {
    $colors = [
      'polygonFill' => '#EE9900',
      'polygonLine' => '#CC5252',
    ];
    array_walk(
      $colors,
      function (&$hexColor) {
        $hexColor = substr($hexColor, 1);
        $hexColor = hexdec($hexColor);
        $hexColor = [
          ($hexColor & 0xFF0000) >> 16,
          ($hexColor & 0x00FF00) >> 8,
          $hexColor & 0x0000FF,
        ];
      }
    );

    return (object) [
      'styleId' => 'projectareastyle',
      'rules' => [
        (object) [
          'conditions' => (object) [
            'geometry' => (object) [
              'type' => 'Polygon',
            ],
          ],
          'style' => (object) [
            'polygonStrokeColor' => array_merge($colors['polygonLine'], [0.8]),
            'polygonFillColor' => array_merge($colors['polygonFill'], [0]),
            'polygonStrokeWidth' => 3,
            'polygonStrokeCap' => 'round',
          ],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return ['dipas:projectareastyle'];
  }

}
