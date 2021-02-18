<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Form;

use Drupal\Core\Form\FormStateInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * Trait ElementValidateFileExistsTrait.
 *
 * Contains a element validate function to validate form input
 * as an existing file.
 *
 * @package Drupal\masterportal
 */
trait ElementValidateFileExistsTrait {

  /**
   * Element validator: checks the existence of a given file.
   *
   * @param array $element
   *   The form element to validate.
   * @param FormStateInterface $form_state
   *   The FormStateInterface object of the current form.
   */
  public function validateFileExists(array &$element, FormStateInterface $form_state) {
    if (!empty($element['#value'])) {
      if (preg_match('~^https?://~i', $element['#value'])) {
        // Remote file
        try {
          $result = \Drupal::service('http_client')->request(
            'HEAD',
            $element['#value'],
            ['allow_redirects' => TRUE]
          );
          $element['#real_file_path'] = $element['#value'];
        }
        catch (RequestException $e) {
          $form_state->setError(
            $element,
            $this->t(
              'The remote address given in %element returned a %http_status status code!',
              [
                '%element' => $element['#title'],
                '%http_status' => $e->getCode(),
              ],
              ['context' => 'Masterportal']
            )
          );
        }
      }
      else {
        // Local file
        if ($this->tokenService->containsTokens($element['#value'])) {
          $this->tokenService->setFileSystemTokenReplacement(TRUE);
          $pathToCheck = realpath(sprintf(
            '%s/%s',
            DRUPAL_ROOT,
            $this->tokenService->replaceTokens($element['#value'])
          ));
        }
        else {
          $pathToCheck = $element['#value'];
        }
        if (!file_exists($pathToCheck)) {
          $form_state->setError(
            $element,
            $this->t('The file path given in %element does not exist!', ['%element' => $element['#title']], ['context' => 'Masterportal'])
          );
        }
        else {
          $element['#real_file_path'] = $pathToCheck;
        }
      }
    }
  }

}
