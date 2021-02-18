<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Trait ElementValidateJsonTrait.
 *
 * Contains a element validate function to validate form input
 * as valid JSON data.
 *
 * @package Drupal\masterportal
 */
trait ElementValidateJsonTrait {

  /**
   * Element validator function to make sure the input contains valid JSON data.
   *
   * @param array $element
   *   The form element to validate.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function validateJsonInput(array &$element, FormStateInterface $form_state) {
    if (!empty($element["#value"])) {
      if (!$this->validateJsonString($element["#value"])) {
        $form_state->setError(
          $element,
          sprintf('The "%s" element does not contain valid JSON.', $element['#title'])
        );
      }
      else {
        $element['#valid_json'] = TRUE;
        if (isset($element['#json_pretty_print'])) {
          $jsonOptions = JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES;
          $prettyPrinted = json_encode(json_decode($element["#value"]), $jsonOptions);
          $element['#value'] = $prettyPrinted;
          $form_state->setValueForElement($element, $prettyPrinted);
        }
      }
    }
  }

  /**
   * Validates a given string for a valid JSON format.
   *
   * @param string $string
   *   The string to validate.
   *
   * @return bool
   *   TRUE, if the string given is in a valid JSON format.
   */
  public function validateJsonString($string) {
    if (!is_string($string) || json_decode($string) === NULL) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Element validate function to verify a given file contains valid JSON.
   *
   * @param array $element
   *   The element to validate.
   * @param FormStateInterface $form_state
   *   The FormStateInterface object.
   */
  public function validateJsonFile(array &$element, FormStateInterface $form_state) {
    // Only perform the validation, when previously the "file exists" check was done.
    if (!isset($element['#real_file_path'])) {
      return;
    }

    // Get the file's contents.
    if (preg_match('~^https?://~i', $element['#real_file_path'])) {
      $result = \Drupal::service('http_client')->request('GET', $element['#real_file_path']);
      $file_contents = $result->getBody()->getContents();
    }
    else {
      $file_contents = file_get_contents($element['#real_file_path']);
    }

    // Remove potential BOM
    $bom = pack('H*','EFBBBF');
    $file_contents = preg_replace("~^$bom~", '', $file_contents);

    // Validate the contents for valid JSON.
    if (!$this->validateJsonString($file_contents)) {
      $form_state->setError(
        $element,
        'The given location does not contain valid JSON.'
      );
    }
    else {
      $element['#valid_json'] = TRUE;
    }
  }

}
