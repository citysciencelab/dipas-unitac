<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class BasicSettings.
 *
 * Defines the basic setting options for the Masterportal integration.
 *
 * @package Drupal\masterportal\Form
 */
class BasicSettings extends MasterportalSettingsBase {

  use ElementValidateFileExistsTrait;
  use ElementValidateJsonTrait;

  /**
   * {@inheritdoc}
   */
  protected function getSettingsKey() {
    return 'masterportal.config.basic';
  }

  /**
   * {@inheritdoc}
   */
  final public function getEditableConfigNames() {
    return [$this->getSettingsKey()];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array $form, FormStateInterface $form_state) {

    $form['html_structure'] = [
      '#type' => 'textarea',
      '#rows' => 15,
      '#title' => $this->t('HTML structure', [], ['context' => 'Masterportal']),
      '#description' => $this->t(
        'Insert the HTML structure to use when a Masterportal map should get integrated. @availabletokens',
        [
          '@availabletokens' => $this->tokenService->pathTokens([
            'module_path',
            'library_path',
            'masterportal_instance',
            'config.json',
            'layerdefinitions.json',
            'services.json',
            'layerstyles.json',
          ]),
        ],
        ['context' => 'Masterportal']
      ),
      '#required' => TRUE,
      '#default_value' => $this->html_structure,
      '#element_validate' => [[$this, 'replaceHtmlTokens']],
      '#attributes' => ['style' => 'font-family: Courier, Courier New, monospace;'],
    ];

    $form['js'] = [
      '#type' => 'textarea',
      '#rows' => 15,
      '#title' => $this->t('Basic Javascript configuration', [], ['context' => 'Masterportal']),
      '#description' => $this->t(
        'Insert the contents of the Javascript settings file. Will automatically be transformed to JSON format. @availabletokens',
        [
          '@availabletokens' => $this->tokenService->pathTokens(
            ['masterportal.js', 'config.js', 'style.css'],
            FALSE
          ),
        ],
        ['context' => 'Masterportal']
      ),
      '#required' => TRUE,
      '#default_value' => $this->js,
      '#element_validate' => [
        [$this, 'convertJavascriptToJson'],
        [$this, 'validateJsonInput'],
        [$this, 'validatePaths'],
      ],
      '#json_pretty_print' => TRUE,
      '#attributes' => ['style' => 'font-family: Courier, Courier New, monospace;'],
    ];

    $form['service_definitions'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Service definitions', [], ['context' => 'Masterportal']),
      '#description' => $this->t(
        'Local path to the service definition file. File must exist and contain valid JSON. @availabletokens',
        ['@availabletokens' => $this->tokenService->availableTokens(['masterportal_instance'])],
        ['context' => 'Masterportal']
      ),
      '#required' => TRUE,
      '#default_value' => $this->service_definitions,
      '#element_validate' => [
        [$this, 'validateFileExists'],
        [$this, 'validateJsonFile'],
      ],
      '#cache_tag' => 'Services',
    ];

    return $form;
  }

  /**
   * Misusing element validate to transform data entered.
   *
   * @param array $element
   *   The form element.
   * @param FormStateInterface $form_state
   *   The FormStateInterface object.
   */
  public function replaceHtmlTokens(array &$element, FormStateInterface $form_state) {
    $value = $this->tokenService->replaceTokens(
      $element['#value'],
      $this->tokenService->getTokens(
        'path',
        [
          'module_path',
          'library_path',
          'masterportal_instance',
          'config.json',
          'layerdefinitions.json',
          'services.json',
          'layerstyles.json'
        ]
      ),
      FALSE
    );
    $element['#value'] = $value;
    $form_state->setValueForElement($element, $value);
  }

  /**
   * Misusing element validate to transform data entered.
   *
   * It should be possible to just paste the contents of the config.js into
   * this field, but this field's content is validated as JSON. So we need
   * to transform the data to JSON first.
   *
   * @param array $element
   *   The form element.
   * @param FormStateInterface $form_state
   *   The FormStateInterface object.
   */
  public function convertJavascriptToJson(array &$element, FormStateInterface $form_state) {
    $value = trim($element['#value'], "\r\n\t ");
    if (preg_match('~^const\s+Config\s*=\s*\{~i', $value)) {
      // Replace all that Javascript rubbish and reformat
      // the keys to comply to JSON standards.
      $value = preg_replace('~^const\s+Config\s*=\s*\{~im', '{', $value);
      $value = preg_replace('~\}\s*;$~im', '}', $value);

      // Remove comments (not allowed in JSON).
      $value = preg_replace('~(\/\/.*?$)~m', '', $value);

      // Encapsulate property keys in quotation marks.
      $value = preg_replace('~(?:^|\b) (?<![\'"\-]) (\w+) (?![\'"]) :~mx', '"\1":', $value);

      // Remove empty lines.
      $value = explode(PHP_EOL, $value);
      array_walk($value, function (&$line) {
        $line = trim($line, "\r\n\t ");
      });
      $value = array_filter($value, function ($line) {
        return !empty($line);
      });
      $value = implode(PHP_EOL, $value);
    }
    $element['#value'] = $value;
    $form_state->setValueForElement($element, $value);
  }

  /**
   * Validates, if the necessary paths are included in the configuration entered.
   *
   * This validator also replaces the available meta tokens with their
   * respective value if the validation succeeded.
   *
   * @param array $element
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function validatePaths(array &$element, FormStateInterface $form_state) {
    $tokens = array_keys(
      $this->tokenService->getTokens(
        'all', ['masterportal.js', 'config.js', 'style.css']
      )
    );
    $pattern = sprintf(
      '~\{\{(?:%s)\}\}~',
      implode('|', array_map(function ($token) {
        return preg_quote($token, '~');
      }, $tokens))
    );
    if (!preg_match($pattern, $element['#value'])) {
      $form_state->setError(
        $element,
        $this->t('The paths to the configuration files are missing!', [], ['context' => 'Masterportal'])
      );
    }
    else {
      $form_state->setValueForElement(
        $element,
        $this->tokenService->replaceTokens(
          $element['#value'],
          $this->tokenService->getTokens('path'),
          FALSE,
          ['masterportal_instance', 'query_params']
        )
      );
    }
  }

}
