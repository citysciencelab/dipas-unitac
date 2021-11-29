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
 *   id = "allprojectareasstyle",
 *   title = @Translation("GeoJSON layer styles for the DIPAS project area layer.")
 * )
 *
 * @package Drupal\dipas\Plugin\Masterportal\LayerStyle
 */
class AllProjectAreasStyles implements LayerStylePluginInterface {

  /**
   * {@inheritdoc}
   */
  public function getStyleObject() {
    $colors = [
      'aktivFill' => '#61bd2b',
      'aktivLine' => '#61bd2b',
      'inaktivFill' => '#3D3D3D',
      'inaktivLine' => '#3D3D3D',
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
      'styleId' => 'allprojectareasstyle',
      'rules' => [
        (object) [
          'conditions' => (object) [
            'properties' => (object) [
              'status' => 'aktiv',
            ],
          ],
          'style' => (object) [
            'polygonStrokeColor' => array_merge($colors['aktivLine'], [0.8]),
            'polygonFillColor' => array_merge($colors['aktivFill'], [0.5]),
            'polygonStrokeWidth' => 3,
            'polygonStrokeCap' => 'round',
          ],
        ],
        (object) [
          'conditions' => (object) [
            'properties' => (object) [
              'status' => 'inaktiv',
            ],
          ],
          'style' => (object) [
            'polygonStrokeColor' => array_merge($colors['inaktivLine'], [0.8]),
            'polygonFillColor' => array_merge($colors['inaktivFill'], [0.5]),
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
