<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Class Layerconfiguration.
 *
 * Contains the layer definitions for Materportal integrations.
 *
 * @package Drupal\masterportal\Form
 */
class Layerconfiguration extends MasterportalSettingsBase {

  use ElementValidateFileExistsTrait;
  use ElementValidateJsonTrait;

  /**
   * @var string
   */
  protected $configurationKey;

  /**
   * {@inheritdoc}
   */
  protected function prepareForm() {
    $this->configurationKey = sprintf('masterportal.config.layers.%s', $this->getActiveDomain());
  }

  /**
   * {@inheritdoc}
   */
  protected function getSettingsKey() {
    return $this->configurationKey;
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

    $form['static_layer_definitions'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Static layer definitions', [], ['context' => 'Masterportal']),
      '#description' => $this->t(
        'Path to the static layer definition file. Can be remote (location starts with http). File must exist and contain valid JSON. @availabletokens',
        ['@availabletokens' => $this->tokenService->availableTokens(['masterportal_instance'])],
        ['context' => 'Masterportal']
      ),
      '#required' => TRUE,
      '#default_value' => $this->static_layer_definitions,
      '#element_validate' => [
        [$this, 'validateFileExists'],
        [$this, 'validateJsonFile'],
        [$this, 'validateLayerDefinitions'],
      ],
      '#cache_tag' => 'LayerDefinitions',
    ];

    $form['custom_layers'] = [
      '#type' => 'textarea',
      '#rows' => 30,
      '#title' => $this->t('Custom layer definitions in JSON format', [], ['context' => 'Masterportal']),
      '#description' => $this->t(
        'Insert custom layer definitions in a valid JSON format defining the custom layers to integrate in a Masterportal map. @availabletokens',
        ['@availabletokens' => $this->tokenService->availableTokens(['masterportal_instance'], $this->tokenService->getTokens('layer'))],
        ['context' => 'Masterportal']
      ),
      '#required' => FALSE,
      '#default_value' => $this->custom_layers,
      '#element_validate' => [
        [$this, 'validateJsonInput'],
        [$this, 'replaceMetaTokens'],
      ],
      '#json_pretty_print' => TRUE,
      '#attributes' => ['style' => 'font-family: Courier, Courier New, monospace;'],
    ];

    $form['preview'] = [
      '#weight' => 999999,
      '#type' => 'html_tag',
      '#tag' => 'p',
      [
        '#type' => 'markup',
        '#markup' => $this->t(
          'Click the @testlink to get a preview of this feed in a new browser tab.',
          [
            '@testlink' => Link::fromTextAndUrl(
              'Testlink',
              Url::fromRoute(
                'masterportal.layerdefinitions',
                [],
                [
                  'attributes' => ['target' => '_blank'],
                ]
              ))->toString(),
          ],
          ['context' => 'Masterportal']
        ),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function postSave(array $values) {
    $this->layerService->checkForLayerChanges($values['Layerconfiguration']['static_layer_definitions']);
  }

  /**
   * Element validate callback.
   *
   * @param array $element
   *   The form element to validate.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   */
  public function validateLayerDefinitions(array &$element, FormStateInterface $form_state) {
    if (!isset($element['#valid_json'])) {
      // Only perform checks if the source previously
      // was checked to be valid JSON.
      return;
    }
    elseif (($message = $this->layerService->checkLayerDefinitions($element['#real_file_path'])) !== TRUE) {
      $form_state->setError($element, $message);
    }
  }

  /**
   * Misusing the element validate to pre-replace meta tokens.
   *
   * @param array $element
   *   The form element to validate.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function replaceMetaTokens(array &$element, FormStateInterface $form_state) {
    $element['#value'] = $this->tokenService->replaceTokens(
      $element['#value'],
      $this->tokenService->getTokens('layer'),
      TRUE,
      ['query_params']
    );
    $form_state->setValueForElement(
      $element,
      $element['#value']
    );
  }

}
