<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\SettingsSection;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Class Export.
 *
 * @SettingsSection(
 *   id = "Export",
 *   title = @Translation("Visibility settings / Export"),
 *   description = @Translation("Proceeding visibility settings and CSV data exports."),
 *   weight = 99,
 *   affectedConfig = {}
 * )
 *
 * @package Drupal\dipas\Plugin\SettingsSection
 */
class Export extends SettingsSectionBase {

  /**
   * Determines if a proceeding should get exposed on public APIs.
   *
   * @var bool
   */
  protected $proceeding_is_internal;

  /**
   * {@inheritdoc}
   */
  public static function getDefaults() {
    return [
      'proceeding_is_internal' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array $form, FormStateInterface $form_state) {
    return [
      'visibility_settings' => [
        '#type' => 'fieldset',
        '#title' => $this->t('Visibility settings', [], ['context' => 'DIPAS']),

        'proceeding_is_internal' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Proceeding is internal', [], ['context' => 'DIPAS']),
          '#description' => $this->t('If checked, this proceeding will not be exposed on public APIs (i.e. the PDS-API).', [], ['context' => 'DIPAS']),
          '#default_value' => $this->proceeding_is_internal,
        ],
      ],
      'data_exports' => [
        '#type' => 'fieldset',
        '#title' => $this->t('Data exports', [], ['context' => 'DIPAS']),

        'contributions' => [
          '#type' => 'html_tag',
          '#tag' => 'a',
          '#value' => $this->t('Export contributions', [], ['context' => 'DIPAS']),
          '#attributes' => [
            'href' => Url::fromRoute('dipas.export', ['type' => 'contributions'])->toString(),
            'target' => '_blank',
            'class' => ['button'],
            'style' => 'display: block; width: 100%; margin: 20px auto 0;',
          ],
        ],
        'contribution_comments' => [
          '#type' => 'html_tag',
          '#tag' => 'a',
          '#value' => $this->t('Export contribution (phase 1) comments', [], ['context' => 'DIPAS']),
          '#attributes' => [
            'href' => Url::fromRoute('dipas.export', ['type' => 'contribution_comments'])->toString(),
            'target' => '_blank',
            'class' => ['button'],
            'style' => 'display: block; width: 100%; margin: 20px auto 0;',
          ],
        ],
        'conception_comments' => [
          '#type' => 'html_tag',
          '#tag' => 'a',
          '#value' => $this->t('Export conception (phase 2) comments', [], ['context' => 'DIPAS']),
          '#attributes' => [
            'href' => Url::fromRoute('dipas.export', ['type' => 'conception_comments'])->toString(),
            'target' => '_blank',
            'class' => ['button'],
            'style' => 'display: block; width: 100%; margin: 20px auto 0;',
          ],
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
      'proceeding_is_internal' => (bool) $plugin_values['visibility_settings']['proceeding_is_internal'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function hasConfigurationSettings() {
    return TRUE;
  }

}
