<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\SettingsSection;

use Drupal\Component\DependencyInjection\Container;
use Drupal\Core\Form\FormStateInterface;
use Drupal\masterportal\DomainAwareTrait;

/**
 * Class ProjectSchedule.
 *
 * @SettingsSection(
 *   id = "ProjectSchedule",
 *   title = @Translation("Project schedule"),
 *   description = @Translation("Settings for the project phase schedule."),
 *   weight = 5,
 *   affectedConfig = {}
 * )
 *
 * @package Drupal\dipas\Plugin\SettingsSection
 */
class ProjectSchedule extends SettingsSectionBase {

  use DomainAwareTrait;
  use NodeSelectionTrait;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * {@inheritdoc}
   */
  protected function setAdditionalDependencies(Container $container) {
    $this->nodeStorage = $container->get('entity_type.manager')->getStorage('node');
  }

  /**
   * {@inheritdoc}
   */
  public static function getDefaults() {
    return [
      'project_start' => '',
      'project_end' => '',
      'phase_2_enabled' => FALSE,
      'phasemix_enabled' => FALSE,
      'phase_2_start' => '',
      'conceptionpage' => '',
      'allow_conception_comments' => TRUE,
      'display_existing_conception_comments' => TRUE,
      'overwriteFrontpage' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array $form, FormStateInterface $form_state) {
    $page_options = $this->getPageOptions();

    return [
      'project_start' => [
        '#type' => 'date',
        '#title' => $this->t('Starting date phase 1 (contributions)', [], ['context' => 'DIPAS']),
        '#description' => $this->t('Select the starting date, on which the website will start to collect contributions.', [], ['context' => 'DIPAS']),
        '#required' => TRUE,
        '#default_value' => $this->project_start,
      ],
      'phase_2_enabled' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Enable phase 2', [], ['context' => 'DIPAS']),
        '#required' => FALSE,
        '#default_value' => $this->phase_2_enabled,
      ],
      'phase_2_settings' => [
        '#type' => 'fieldset',
        '#title' => $this->t('Settings for project phase 2'),
        '#states' => [
          'visible' => [':input[type="checkbox"][name="settings[ProjectSchedule][phase_2_enabled]"]' => ['checked' => TRUE]],
        ],
        'phase_2_start' => [
          '#type' => 'date',
          '#title' => $this->t('Starting date phase 2 (conceptions)', [], ['context' => 'DIPAS']),
          '#description' => $this->t('Select the starting date, on which the website will start to display conceptions.', [], ['context' => 'DIPAS']),
          '#default_value' => $this->phase_2_start,
          '#element_validate' => [['\Drupal\dipas\Plugin\SettingsSection\ProjectSchedule', 'verifyPhase2Date']],
          '#states' => [
            'required' => [':input[type="checkbox"][name="settings[ProjectSchedule][phase_2_enabled]"]' => ['checked' => TRUE]],
          ],
        ],
        'conceptionpage' => [
          '#type' => 'select',
          '#title' => $this->t('Conceptions page', [], ['context' => 'DIPAS']),
          '#description' => $this->t('Select the page that provides the overview of all conceptions.', [], ['context' => 'DIPAS']),
          '#options' => $page_options,
          '#default_value' => $this->conceptionpage,
          '#states' => [
            'required' => [':input[type="checkbox"][name="settings[ProjectSchedule][phase_2_enabled]"]' => ['checked' => TRUE]],
          ],
        ],
        'allow_conception_comments' => [
          '#title' => $this->t('Allow comments on conceptions', [], ['context' => 'DIPAS']),
          '#description' => $this->t('When activated, users can write comments on published conceptions', [], ['context' => 'DIPAS']),
          '#type' => 'checkbox',
          '#default_value' => $this->allow_conception_comments,
        ],
        'display_existing_conception_comments' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Keep displaying existing comments', [], ['context' => 'DIPAS']),
          '#description' => $this->t('Should already existing comments still be displayed or should they get hidden?', [], ['context' => 'DIPAS']),
          '#default_value' => $this->display_existing_conception_comments,
          '#states' => [
            'visible' => [
              ':input[type="checkbox"][name="settings[ProjectSchedule][phase_2_settings][allow_conception_comments]"]' => ['checked' => FALSE],
            ]
          ],
          '#attributes' => [
            'style' => 'margin-left: 25px;',
          ],
        ],
        'phasemix_enabled' => [
          '#type' => 'radios',
          '#title' => $this->t('Contribution form status', [], ['context' => 'DIPAS']),
          '#description' => $this->t('Select the status of the contribution form during phase 2.', [], ['context' => 'DIPAS']),
          '#options' => [
            0 => $this->t('closed', [], ['context' => 'DIPAS']),
            1 => $this->t('open (Phasemix)', [], ['context' => 'DIPAS']),
          ],
          '#default_value' => (int) $this->phasemix_enabled,
        ],
      ],
      'project_end' => [
        '#type' => 'date',
        '#title' => $this->t('Ending date', [], ['context' => 'DIPAS']),
        '#description' => $this->t('Select the date on which the website will transition to the frozen state.', [], ['context' => 'DIPAS']),
        '#required' => TRUE,
        '#default_value' => $this->project_end,
        '#element_validate' => [['\Drupal\dipas\Plugin\SettingsSection\ProjectSchedule', 'verifyEndDate']],
      ],
    ];
  }

  /**
   * Element validation callback.
   *
   * Validates if the starting date of phase 2 entered is between the starting and the ending date.
   *
   * @param array $element
   *   The element to validate.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The FormStateInterface object of the form submitted.
   */
  public static function verifyPhase2Date(array $element, FormStateInterface $form_state) {
    if ((bool) $form_state->getValues()['settings']['ProjectSchedule']['phase_2_enabled']) {
      $start = strtotime($form_state->getValues()['settings']['ProjectSchedule']['project_start']);
      $end = strtotime($form_state->getValues()['settings']['ProjectSchedule']['project_end']);
      $phase2start = strtotime($element['#value']);
      if ($phase2start <= $start || $phase2start >= $end) {
        $form_state->setError($element, t('The starting date for phase 2 has to be inbetween the project start and end dates.', [], ['context' => 'DIPAS']));
      }
    }
  }

  /**
   * Element validation callback.
   *
   * Validates if the end date entered is after the starting date.
   *
   * @param array $element
   *   The element to validate.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The FormStateInterface object of the form submitted.
   */
  public static function verifyEndDate(array $element, FormStateInterface $form_state) {
    $start = strtotime($form_state->getValues()['settings']['ProjectSchedule']['project_start']);
    $end = strtotime($element['#value']);
    if ($end <= $start) {
      $form_state->setError($element, t('The ending date has to be after the starting date.', [], ['context' => 'DIPAS']));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function onSubmit() {}

  /**
   * {@inheritdoc}
   */
  public static function getProcessedValues(array $plugin_values, array $form_values) {
    return [
      'project_start' => $plugin_values['project_start'],
      'project_end' => $plugin_values['project_end'],
      'phase_2_enabled' => (bool) $plugin_values['phase_2_enabled'],
      'phasemix_enabled' => (bool) $plugin_values['phase_2_settings']['phasemix_enabled'],
      'phase_2_start' => $plugin_values['phase_2_settings']['phase_2_start'],
      'conceptionpage' => (int) $plugin_values['phase_2_settings']['conceptionpage'],
      'allow_conception_comments' => (bool) $plugin_values['phase_2_settings']['allow_conception_comments'],
      'display_existing_conception_comments' => (bool) $plugin_values['phase_2_settings']['allow_conception_comments'] === FALSE
                                                  ? (bool) $plugin_values['phase_2_settings']['display_existing_conception_comments']
                                                  : TRUE,
    ];
  }
}
