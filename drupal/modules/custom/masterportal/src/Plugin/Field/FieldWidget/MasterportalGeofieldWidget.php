<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\geofield\Plugin\Field\FieldWidget\GeofieldLatLonWidget;
use Drupal\masterportal\EnsureObjectStructureTrait;

/**
 * Field widget implementation to use the Masterportal as an input device.
 *
 * @FieldWidget(
 *   id = "masterportal_geofield_widget",
 *   label = @Translation("Masterportal"),
 *   field_types = {
 *     "geofield"
 *   },
 *   multiple_values = FALSE,
 * )
 */
class MasterportalGeofieldWidget extends GeofieldLatLonWidget {

  use EnsureObjectStructureTrait;
  use MasterportalWidgetTrait;

  const INTEGRATE_PARENT_SETTINGS = TRUE;

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

    if (
      !empty($formElement["value"]["#default_value"]['lon']) &&
      !empty($formElement["value"]["#default_value"]['lat'])
    ) {
      $coords = sprintf(
        '%s,%s',
        $formElement["value"]["#default_value"]['lon'],
        $formElement["value"]["#default_value"]['lat']
      );
    }
    else {
      $coords = FALSE;
    }

    // Add the Masterportal as part of the widget.
    $formElement['masterportalWidget'] = [
      '#title' => $formElement['value']['#title'],
      '#description' => $this->t('Click in the map to set a location.', [], ['context' => 'Masterportal']),
      '#description_display' => 'before',
      '#theme_wrappers' => ['fieldset'],
      '#attributes' => [
        'class' => ['masterportalWidgetMap'],
        'data-fieldname' => $items->getName(),
        'data-fieldtype' => 'GeofieldLatLon',
      ],
      '#attached' => [
        'library' => [
          'masterportal/masterportalWidget',
        ],
      ],
      $this->masterportalRenderer->iframe(
        $this->instanceService->loadInstance($this->getSetting('masterportal_instance')),
        sprintf('%s%s', $this->getSetting('width'), $this->getSetting('unit')),
        $this->getSetting('aspect_ratio'),
        $coords !== FALSE ? $this->getSetting('editingZoomLevel') : NULL,
        $coords !== FALSE ? $coords : NULL,
        $coords !== FALSE ? $coords : NULL
      )
    ];

    if ($element["#required"]) {
      $formElement["masterportalWidget"]["#attributes"]["class"][] = 'form-required';
    }

    return $formElement;
  }

}
