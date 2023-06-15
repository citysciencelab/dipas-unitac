<?php

namespace Drupal\masterportal\Form;

use Drupal\Core\Form\FormStateInterface;

class MapProjections extends MasterportalSettingsBase {

  use MultivalueRowTrait;

  /**
   * @var string
   */
  protected $configurationKey;

  /**
   * {@inheritdoc}
   */
  protected function prepareForm() {
    $this->configurationKey = sprintf('masterportal.config.%s.projections', $this->getActiveDomain());
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
    if (!is_array($this->projections) || !count($this->projections)) {
      $basicConfig = $this->configFactory->get('masterportal.config.basic');
      $jsSettings = (array) json_decode($basicConfig->get('BasicSettings')['js']);

      if (isset($jsSettings['namedProjections']) && is_array($jsSettings['namedProjections'])) {
        $this->projections = array_map(
          function ($projection) {
            return [
              'name' => $projection[0],
              'definition' => $projection[1],
            ];
          },
          $jsSettings['namedProjections']
        );
      }
    }

    $form = [
      '#tree' => TRUE,
      'projections' => [
        '#type' => 'fieldgroup',
        '#title' => $this->t('Configured Map projections', [], ['context' => 'Masterportal']),
      ],
    ];

    $this->createMultivalueFormPortion(
      $form['projections'],
      'projections',
      $form_state,
      $this->projections ?? [],
      'No map projections defined. Click the "Add projection" button to add a new map projection.'
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function getInputRow($property, $delta, array $row_defaults, FormStateInterface $form_state) {
    return [
      'name' => [
        '#type' => 'textfield',
        '#title' => $this->t('Projection name', [], ['context' => 'Masterportal']),
        '#description' => $this->t('The name this projection will be listed with in selections', [], ['context' => 'Masterportal']),
        '#required' => TRUE,
        '#default_value' => $row_defaults['name'] ?? NULL,
        '#maxlength' => 15,
        '#size' => 15,
      ],
      'definition' => [
        '#type' => 'textfield',
        '#title' => $this->t('Projection definition', [], ['context' => 'Masterportal']),
        '#description' => $this->t('The definition string of this projection that will get provided to the Masterportal', [], ['context' => 'Masterportal']),
        '#required' => TRUE,
        '#default_value' => $row_defaults['definition'] ?? NULL,
        '#maxlength' => 250,
        '#size' => 150,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function preSave($key, &$value, array $formPortion, FormStateInterface $form_state) {
    $value = self::getData($key, $form_state->getUserInput());
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
    return 'Add projection';
  }

  /**
   * {@inheritdoc}
   */
  protected function allowMultipleEmptyAdds($property) {
    return FALSE;
  }

}
