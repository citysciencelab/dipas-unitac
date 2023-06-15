<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\SettingsSection;

use Drupal\Component\DependencyInjection\Container;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ProjectArea.
 *
 * @SettingsSection(
 *   id = "ProjectArea",
 *   title = @Translation("Project area"),
 *   description = @Translation("Define the area of this project on a map."),
 *   weight = 10,
 *   affectedConfig = {}
 * )
 *
 * @package Drupal\dipas\Plugin\SettingsSection
 */
class ProjectArea extends SettingsSectionBase {

  /**
   * @var \Drupal\masterportal\Service\InstanceServiceInterface
   */
  protected $masterportalInstanceService;

  /**
   * @var \Drupal\masterportal\Service\MasterportalInterface
   */
  protected $masterportalRenderer;

  /**
   * {@inheritdoc}
   */
  protected function setAdditionalDependencies(Container $container) {
    $this->masterportalInstanceService = $container->get('masterportal.instanceservice');
    $this->masterportalRenderer = $container->get('masterportal.renderer');
  }

  /**
   * {@inheritdoc}
   */
  public static function getDefaults() {
    return [
      'project_area' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array $form, FormStateInterface $form_state) {
    $value = $this->t(
      empty($this->project_area) ? 'Create project area' : 'Modify project area', [], ['context' => 'DIPAS']
    );
    return [
      'project_area' => [
        '#type' => 'textarea',
        '#default_value' => $this->project_area,
        '#attributes' => [
          'style' => 'display: none;',
        ],
        '#required' => TRUE,
      ],
      'project_area_centerpoint' => [
        '#type' => 'textfield',
        '#default_value' => $this->project_area_centerpoint,
        '#attributes' => [
          'style' => 'display: none;',
        ],
        '#required' => TRUE,
      ],
      'create_projectarea' => [
        '#type' => 'button',
        '#executes_submit_callback' => FALSE,
        '#limit_validation_errors' => [],
        '#value' => $value,
        '#attributes' => [
          'class' => ['initProjectArea'],
          'style' => 'display: none; margin-bottom: 20px;',
        ],
      ],
      'projectarea_text' => [
        '#type' => 'html_tag',
        '#tag' => 'label',
        '#value' => $this->t('Project area must be defined to save configuration.', [], ['context' => 'DIPAS']),
        '#attributes' => [
          'style' => empty($this->project_area) ? 'display: block; color: #EE0000;' : 'display: none;',
        ],
      ],
      'masterportal_project_area' => $this->masterportalRenderer->iframe(
        $this->masterportalInstanceService->loadInstance('default.dipas_projectarea'),
        '100%',
        'aspect_ratio_16_9'
      ),
      '#attached' => [
        'library' => [
          'dipas/projectareamap',
        ],
      ],
    ];
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
      'project_area' => $plugin_values['project_area'],
      'project_area_centerpoint' => $plugin_values['project_area_centerpoint'],
    ];
  }

}
