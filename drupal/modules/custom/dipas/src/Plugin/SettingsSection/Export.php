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
 *   title = @Translation("Data export"),
 *   description = @Translation("Export user generated data as CSV."),
 *   weight = 99,
 *   affectedConfig = {}
 * )
 *
 * @package Drupal\dipas\Plugin\SettingsSection
 */
class Export extends SettingsSectionBase {

  /**
   * {@inheritdoc}
   */
  public static function getDefaults() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array $form, FormStateInterface $form_state) {
    return [
      'contributions' => [
        '#type' => 'html_tag',
        '#tag' => 'a',
        '#value' => $this->t('Export contributions', [], ['context' => 'DIPAS']),
        '#attributes' => [
          'href' => Url::fromRoute('dipas.export', ['type' => 'contributions'])->toString(),
          'target' => '_blank',
          'class' => ['button'],
          'style' => 'display: block; width: 100%; margin-top: 20px;',
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
          'style' => 'display: block; width: 100%; margin-top: 20px;',
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
          'style' => 'display: block; width: 100%; margin-top: 20px;',
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
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function hasConfigurationSettings() {
    return FALSE;
  }

}
