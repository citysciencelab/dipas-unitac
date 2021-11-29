<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\geofield\Element\GeofieldLatLon;
use Drupal\masterportal\EnsureObjectStructureTrait;

/**
 * Class MasterportalGeofieldLatLng.
 *
 * Custom element validator for GeofieldLatLng widgets. Implemented to
 * "move" potential form errors to the Masterportal map instead targeting
 * invisible form elements.
 *
 * @package Drupal\masterportal\Element
 */
class MasterportalGeofieldLatLng extends GeofieldLatLon {

  use EnsureObjectStructureTrait;

  /**
   * {@inheritdoc}
   */
  public static function latlonProcess(array &$element, FormStateInterface $form_state, array &$complete_form) {
    $element = parent::latlonProcess($element, $form_state, $complete_form);
    static::ensureConfigPath($element, '*#attributes->*class');
    // Only change something if it is actually a Masterportal widget.
    if (in_array('masterportalWidget', $element["#attributes"]["class"]) && $element["lat"]['#required']) {
      $element["lat"]['#required'] = FALSE;
      $element["lon"]['#required'] = FALSE;
      $element['#has_required_fields'] = TRUE;
    }
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function elementValidate(array &$element, FormStateInterface $form_state, array &$complete_form) {
    // If it is no Masterportal widget, simply don't
    // validate anything on our own.
    if (!in_array('masterportalWidget', $element["#attributes"]["class"])) {
      return parent::elementValidate($element, $form_state, $complete_form);
    }

    // Determine the root of the Masterportal widget form section.
    $parents = $element["#array_parents"];
    array_pop($parents);

    // Find the Masterportal widget form section.
    $form_element = self::findFormElement($complete_form, $parents);

    // Check, if the field is required and in case it is pre-catch this error.
    if (isset($element["#has_required_fields"]) && (empty($element["lat"]["#value"]) || empty($element["lon"]["#value"]))) {
      return $form_state->setError(
        $form_element["masterportalWidget"],
        t(
          'The field %field is required! Please select a location on the map!',
          ['%field' => $element["#error_label"]]
        )
      );
    }

    // Let parent do it's validation as usual.
    parent::elementValidate($element, $form_state, $complete_form);

    // If there were errors in validating the LatLng fields set by parent,
    // we need to "redirect" these errors to the visible part of the form.
    $all_errors = $form_state->getErrors();

    // Check each contained fields for errors.
    $subErrorsFound = [];
    foreach (static::getComponents() as $key => $component) {
      $suberror = $form_state->getError($element[$key]);
      if (!empty($suberror)) {
        $subErrorsFound[] = $suberror;
        $error_key = implode('][', $element[$key]['#parents']);
        unset($all_errors[$error_key]);
      }
    }

    // Now, remove all errors set until now - we will reset remaining ones
    // after cleanup ourselves.
    $form_state->clearErrors();

    // Re-set remaining errors.
    foreach ($all_errors as $elementName => $error) {
      $form_state->setErrorByName(
        $elementName,
        $error
      );
    }

    // If there were suberrors on this widget, we set a visible error on the map.
    if (!empty($subErrorsFound)) {
      $form_state->setError(
        $form_element["masterportalWidget"],
        t(
          'Please check your input at field %field: @suberrors',
          [
            '%field' => $element["#error_label"],
            '@suberrors' => implode(', ', $subErrorsFound)
          ]
        )
      );
    }

  }

  /**
   * Helper function: finds and returns the widget form portion.
   *
   * @param array $form
   *   The complete form definition to search.
   * @param array $paremts
   *   The array of parent elements of the current widget.
   *
   * @return array
   *   The form portion containing the field widget.
   */
  protected static function findFormElement(array &$form, array &$paremts) {
    $key_to_search = array_shift($paremts);
    foreach (Element::children($form) as $key) {
      if ($key === $key_to_search) {
        if (count($paremts)) {
          return self::findFormElement($form[$key], $paremts);
        }
        else {
          return $form[$key];
        }
      }
    }
  }

}
