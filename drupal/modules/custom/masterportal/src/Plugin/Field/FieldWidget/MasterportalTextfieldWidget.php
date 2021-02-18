<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\StringTextareaWidget;
use Drupal\Core\Form\FormStateInterface;
use Drupal\masterportal\EnsureObjectStructureTrait;

/**
 * Field widget implementation to use the Masterportal as an input device.
 *
 * @FieldWidget(
 *   id = "masterportal_textfield_widget",
 *   label = @Translation("Masterportal"),
 *   field_types = {
 *     "string_long"
 *   }
 * )
 */
class MasterportalTextfieldWidget extends StringTextareaWidget {

  use EnsureObjectStructureTrait;
  use MasterportalWidgetTrait;

  const INTEGRATE_PARENT_SETTINGS = FALSE;

  /**
   * {@inheritdoc}
   */
  public function formElement(
    FieldItemListInterface $items,
    $delta,
    array $element,
    array &$form,
    FormStateInterface $form_state
  ) {
    // Get the form element from Geofield.
    $formElement = parent::formElement($items, $delta, $element, $form, $form_state);

    // Add a CSS class to the default inputs.
    static::ensureConfigPath($formElement, '*value->*#attributes->*class');
    $formElement["value"]['#attributes']['class'][] = 'masterportalWidget';
    $formElement["value"]['#attributes']['class'][] = sprintf('masterportalWidget-%s', $items->getName());

    if (empty($formElement['value']['#default_value']) || ($geometry = json_decode($formElement['value']['#default_value'])) === null) {
      $geometry = FALSE;
    }
    else {
      $iframeUrlParams = [
        'projection' => 'EPSG:4326',
      ];
      switch ($geometry->geometry->type) {
        case 'Point':
          $iframeUrlParams['center'] = sprintf('%s,%s', $geometry->geometry->coordinates[0], $geometry->geometry->coordinates[1]);
          $iframeUrlParams['zoomLevel'] = $this->getSetting('editingZoomLevel');
          break;

        case 'LineString':
        case 'Polygon':
          $extent = $this->calculateExtent($geometry->geometry->type, $geometry->geometry->coordinates);
          $iframeUrlParams['zoomToExtent'] = sprintf(
            '%s,%s,%s,%s',
            $extent['lonMin'],
            $extent['latMin'],
            $extent['lonMax'],
            $extent['latMax']
          );
          break;
      }
    }

    // Add the Masterportal as part of the widget.
    $formElement['masterportalWidget'] = [
      '#title' => $formElement['value']['#title'],
      '#description_display' => 'before',
      '#theme_wrappers' => ['fieldset'],
      '#attributes' => [
        'class' => ['masterportalWidgetMap'],
        'data-fieldname' => $items->getName(),
        'data-fieldtype' => 'Textfield',
      ],
      '#attached' => [
        'library' => [
          'masterportal/masterportalWidget',
        ],
        'drupalSettings' => [
          'masterportal' => [
            $items->getName() => $geometry,
          ],
        ],
      ],
      'drawOptions' => [
        '#weight' => -1,
        '#title' => $this->t('Select the type of drawing you want to make', [], ['context' => 'Masterportal']),
        '#type' => 'radios',
        '#options' => [
          'Point' => $this->t('Point', [], ['context' => 'Masterportal']),
          'LineString' => $this->t('Line', [], ['context' => 'Masterportal']),
          'Polygon' => $this->t('Area', [], ['context' => 'Masterportal'])
        ],
        '#attributes' => [
          'class' => ['geometryType'],
        ],
      ],
      'map' => $this->masterportalRenderer->iframe(
        $this->instanceService->loadInstance($this->getSetting('masterportal_instance')),
        sprintf('%s%s', $this->getSetting('width'), $this->getSetting('unit')),
        $this->getSetting('aspect_ratio'),
        NULL,
        NULL,
        NULL,
        $geometry ? $iframeUrlParams : []
      )
    ];

    return $formElement;
  }

  /**
   * Calculates the extent coordinate values out of the geometry data.
   *
   * @param string $type
   *   The type of geometry data
   * @param array $geometries
   *   The geometries as stated in the geometry data
   *
   * @return float[]|false
   *   The extent coordinates or FALSE if empty.
   */
  protected function calculateExtent($type, array $geometries) {
    $extent = FALSE;

    if (!empty($geometries)) {
      $extent = [
        'lonMin' => 9999999999,
        'latMin' => 9999999999,
        'lonMax' => 0,
        'latMax' => 0,
      ];
      if ($type !== 'Polygon') {
        $geometries = [$geometries];
      }
      foreach ($geometries as $geometry) {
        foreach ($geometry as $coords) {
          list($lon, $lat) = $coords;
          if ($lon < $extent['lonMin']) {
            $extent['lonMin'] = $lon;
          }
          if ($lat < $extent['latMin']) {
            $extent['latMin'] = $lat;
          }
          if ($lon > $extent['lonMax']) {
            $extent['lonMax'] = $lon;
          }
          if ($lat > $extent['latMax']) {
            $extent['latMax'] = $lat;
          }
        }
      }

      $padding = 0.00002;
      $extent['lonMin'] -= $padding;
      $extent['latMin'] -= $padding;
      $extent['lonMax'] += $padding;
      $extent['latMax'] += $padding;
    }

    return $extent;
  }

}
