<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Trait ElementValidateCoordinatesTrait.
 *
 * Contains element validate functions to validate form input
 * as correct and plausible coordinates.
 *
 * @package Drupal\masterportal
 */
trait ElementValidateCoordinatesTrait {

  /**
   * Make sure the string translation trait is present.
   */
  abstract protected function t($string, array $args = [], array $options = []);

  /**
   * Make sure the ElementValidateJsonTrait is also present.
   */
  abstract public function validateJsonInput(array &$element, FormStateInterface $form_state);

  /**
   * Makes sure that the ElementValidateJsonTrait was run first.
   *
   * @param array $element
   *   The form element to validate.
   * @param FormStateInterface $form_state
   *   The FormStateInterface object of the current form.
   *
   * @return bool
   *   TRUE, if the Json validation did run first.
   */
  protected function preCheckJsonValidation(array &$element, FormStateInterface $form_state) {
    $jsonValidationDelta = FALSE;
    $coordinatesCheckDelta = FALSE;
    array_walk($element["#element_validate"], function ($validation, $delta) use (&$jsonValidationDelta, &$coordinatesCheckDelta) {
      switch ($validation[1]) {
        case 'validateJsonInput':
          $jsonValidationDelta = $delta;
          break;

        case 'validateCoords':
        case 'validateExtent':
          $coordinatesCheckDelta = $delta;
          break;
      }
    });
    return $jsonValidationDelta !== FALSE && $jsonValidationDelta < $coordinatesCheckDelta;
  }

  /**
   * Element validation to verify valid coordinates.
   *
   * @param array $element
   *   The form element to validate.
   * @param FormStateInterface $form_state
   *   The FormStateInterface object of the current form.
   * @param array $form
   *   The complete form definition the element resides in.
   * @param int $coordinateItems
   *   The number of coordinate items that have to be present.
   */
  public function validateCoords(array &$element, FormStateInterface $form_state, array $form, $coordinateItems = 2) {
    if (!empty($element['#value'])) {
      if (!$this->preCheckJsonValidation($element, $form_state)) {
        $element['#failedValidation'] = TRUE;
        $form_state->setError(
          $element,
          $this->t(
            'The "@element" element has to be checked for valid JSON data first!',
            ['@element' => $element['#title']],
            ['context' => 'Masterportal']
          )
        );
      }
      else {
        // Decode the JSON entered.
        $coords = json_decode($element["#value"]);

        // The value must represent an array...
        if (!is_array($coords)) {
          $element['#failedValidation'] = TRUE;
          $form_state->setError(
            $element,
            $this->t(
              'The "@element" element does not contain an array.',
              ['@element' => $element['#title']],
              ['context' => 'Masterportal']
            )
          );
        }
        // ...with exactly 4 values...
        elseif (count($coords) !== $coordinateItems) {
          $element['#failedValidation'] = TRUE;
          $form_state->setError(
            $element,
            $this->t(
              'The "@element" element does not contain @count coordinate values.',
              [
                '@element' => $element['#title'],
                '@count' => $coordinateItems,
              ],
              ['context' => 'Masterportal']
            )
          );
        }
        // ...and only integers as array items.
        elseif (!empty(array_filter(
          $coords,
          function ($item) {
            return !is_int($item);
          }
        ))) {
          $element['#failedValidation'] = TRUE;
          $form_state->setError(
            $element,
            $this->t(
              'The "@element" element definition does not contain @count integer values.',
              [
                '@element' => $element['#title'],
                '@count' => $coordinateItems,
              ],
              ['context' => 'Masterportal']
            )
          );
        }
      }
    }
  }

  /**
   * Element validator: checks the existence of a given file.
   *
   * @param array $element
   *   The form element to validate.
   * @param FormStateInterface $form_state
   *   The FormStateInterface object of the current form.
   * @param array $form
   *   The complete form definition the element resides in.
   */
  public function validateExtent(array $element, FormStateInterface $form_state, array $form) {
    if (!empty($element['#value'])) {

      if (!$this->preCheckJsonValidation($element, $form_state)) {
        $form_state->setError(
          $element,
          sprintf('The "%s" element has to be checked for valid JSON data first!', $element['#title'])
        );
      }
      else {

        // Run the coordinate check first (most of the work is done there).
        $this->validateCoords($element, $form_state, $form, 4);

        // Decode the JSON entered.
        $coords = json_decode($element["#value"]);

        // Last check: the items must represent an area (x1 < x2 and y1 < y2).
        if (!isset($element['#failedValidation']) && ($coords[0] >= $coords[2]) || ($coords[1] >= $coords[3])) {
          $form_state->setError(
            $element,
            sprintf('The "%s" element does not represent a valid extent definition (check the coordinates!).', $element['#title'])
          );
        }
      }
    }
  }

}
