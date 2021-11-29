<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class LayerStyles.
 *
 * Contains the style definitions form potion for Materportal integrations.
 *
 * @package Drupal\masterportal\Form
 */
class LayerStyles extends MasterportalSettingsBase {

  use MultivalueRowTrait;
  use StringTranslationTrait;

  /**
   * @var string
   */
  protected $configurationKey;

  /**
   * {@inheritdoc}
   */
  protected function prepareForm() {
    $this->configurationKey = sprintf('masterportal.config.%s.layers', $this->getActiveDomain());
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
    $form = [
      '#tree' => TRUE,
      'layerstyles' => [
        '#type' => 'fieldgroup',
        '#title' => $this->t('Layer styles', [], ['context' => 'Masterportal']),
      ],
    ];

    $this->createMultivalueFormPortion(
      $form['layerstyles'],
      'layerstyles',
      $form_state,
      $this->layerstyles ?: [],
      'No layer styles defined. Click the "Add style" button to add a new layer style.'
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function getInputRow($property, $delta, array $row_defaults, FormStateInterface $form_state) {
    switch (TRUE) {
      case $property === 'layerstyles':
        $rulesection = sprintf('rules_%d', $delta);
        $row = [
          '#tree' => TRUE,
          'styleId' => [
            '#type' => 'textfield',
            '#title' => $this->t('Style ID', [], ['context' => 'Masterportal']),
            '#description' => $this->t('The layer ID this styleset is for.', [], ['context' => 'Masterportal']),
            '#default_value' => !empty($row_defaults['styleId']) ? $row_defaults['styleId'] : '',
          ],
          'rulescontainer' => [
            '#type' => 'details',
            '#title' => $this->t('Rules', [], ['context' => 'Masterportal']),

            $rulesection => [],
          ],
        ];
        unset($row_defaults['styleId']);
        $this->createMultivalueFormPortion(
          $row['rulescontainer'][$rulesection],
          $rulesection,
          $form_state,
          !empty($row_defaults) ? $row_defaults : [],
          'No rules defined. Click the "Add rule" button to add a new rule.'
        );
        return $row;

      case preg_match('~^rules_~', $property) === 1:
        list($rdelta) = sscanf($property, 'rules_%d');
        $conditionsection = sprintf('condition_%d_%d', $rdelta, $delta);
        $stylesection = sprintf('style_%d_%d', $rdelta, $delta);
        $row = [
          'conditions' => [
            '#type' => 'details',
            '#title' => $this->t('Conditions', [], ['context' => 'Masterportal']),
            $conditionsection => [],
          ],
          'styles' => [
            '#type' => 'details',
            '#title' => $this->t('Style', [], ['context' => 'Masterportal']),
            $stylesection => [],
          ],
        ];
        $this->createMultivalueFormPortion(
          $row['conditions'][$conditionsection],
          $conditionsection,
          $form_state,
          !empty($row_defaults['conditions']) ? $row_defaults['conditions'] : [],
          'No conditions defined. Click the "Add condition" button to add a new condition.'
        );
        $this->createMultivalueFormPortion(
          $row['styles'][$stylesection],
          $stylesection,
          $form_state,
          !empty($row_defaults['styles']) ? $row_defaults['styles'] : [],
          'No style values defined. Click the "Add style value" button to add a new styling.'
        );
        return $row;

      case preg_match('~^condition_~', $property) === 1:
        return [
          'property_name' => [
            '#type' => 'textfield',
            '#title' => $this->t('Property name', [], ['context' => 'Masterportal']),
            '#description' => $this->t('You may use dot syntax to address nested properties.', [], ['context' => 'Masterportal']),
            '#default_value' => !empty($row_defaults['property_name']) ? $row_defaults['property_name'] : '',
          ],
          'property_value' => [
            '#type' => 'textfield',
            '#title' => $this->t('Property value', [], ['context' => 'Masterportal']),
            '#default_value' => !empty($row_defaults['property_value']) ? $row_defaults['property_value'] : '',
          ],
        ];

      case preg_match('~^style_~', $property) === 1:
        return [
          'style_property' => [
            '#type' => 'textfield',
            '#title' => $this->t('Style property', [], ['context' => 'Masterportal']),
            '#default_value' => !empty($row_defaults['style_property']) ? $row_defaults['style_property'] : '',
          ],
          'property_value' => [
            '#type' => 'textfield',
            '#title' => $this->t('Property value', [], ['context' => 'Masterportal']),
            '#default_value' => !empty($row_defaults['property_value']) ? $row_defaults['property_value'] : '',
          ],
          'property_is_json' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Property value is in JSON format.', [], ['context' => 'Masterportal']),
            '#default_value' => !empty($row_defaults['property_is_json']) ? $row_defaults['property_is_json'] : FALSE,
          ],
        ];
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function preSave($key, &$value, array $formPortion, FormStateInterface $form_state) {
    if ($key === 'layerstyles') {
      $styles_processed = [];
      $styles_raw = self::getData('layerstyles', $form_state->getUserInput());
      foreach ($styles_raw as $delta => $style) {
        $styles_processed[$delta] = [
          'styleId' => $style['styleId'],
        ];
        foreach ($style['rulescontainer'] as $rindex => $rule) {
          list($rdelta) = sscanf($rindex, 'rules_%d');

          foreach ($rule['multivaluePart']['rows'] as $rowindex => $row) {
            $styles_processed[$delta][$rowindex] = [
              'conditions' => [],
              'styles' => [],
            ];
            $styles_processed[$delta][$rowindex]['conditions'] = [];
            $cond_index = 'condition_'.$rdelta.'_'.$rowindex;
            foreach ($row['row']['conditions']['value'][$cond_index]['multivaluePart']['rows'] as $condition) {
              $styles_processed[$delta][$rowindex]['conditions'][] = [
                'property_name' => $condition['row']['property_name']['value'],
                'property_value' => $condition['row']['property_value']['value'],
              ];
            }
            $styles_processed[$delta][$rowindex]['styles'] = [];
            $style_index = 'style_'.$rdelta.'_'.$rowindex;
            foreach ($row['row']['styles']['value'][$style_index]['multivaluePart']['rows'] as $styleProp) {
              $styles_processed[$delta][$rowindex]['styles'][] = [
                'style_property' => $styleProp['row']['style_property']['value'],
                'property_value' => $styleProp['row']['property_value']['value'],
                'property_is_json' => (bool) $styleProp['row']['property_is_json']['value'],
              ];
            }
          }
        }
      }
      $value = $styles_processed;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getDataToAdd($property, array $current_state, array $user_input, $addSelectorValue, FormStateInterface $form_state) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  protected function getAddRowButtonTitle($property) {
    switch (TRUE) {
      case $property === 'layerstyles':
        return 'Add style';

      case preg_match('~^rules_~', $property) === 1:
        return 'Add rule';

      case preg_match('~^condition_~', $property) === 1:
        return 'Add condition';

      case preg_match('~^style_~', $property) === 1:
        return 'Add style value';
    }
  }

}
