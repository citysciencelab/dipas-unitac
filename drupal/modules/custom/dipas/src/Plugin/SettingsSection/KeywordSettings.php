<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\SettingsSection;

use Drupal\Component\DependencyInjection\Container;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class KeywordSettings.
 *
 * @SettingsSection(
 *   id = "KeywordSettings",
 *   title = @Translation("Keyword settings"),
 *   description = @Translation("Settings for the keyword service."),
 *   weight = 60,
 *   affectedConfig = {},
 *   permissionRequired = "administer keywords"
 * )
 *
 * @package Drupal\dipas\Plugin\SettingsSection
 */
class KeywordSettings extends SettingsSectionBase {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  protected function setAdditionalDependencies(Container $container) {
    $this->entityTypeManager = $container->get('entity_type.manager');
  }

  /**
   * {@inheritdoc}
   */
  public static function getDefaults() {
    return [
      'enabled' => FALSE,
      'mode' => 'external', // manual, internal, external
      'service_url' => 'https://civitasdigitalis.fortiss.org/demo/keyword-service/keywords',
      'externalService' => 'Leipzig', // Leipzig, DBPedia,
      'number_of_keywords' => 4, // number of keywords
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array $form, FormStateInterface $form_state) {
    return  [
      'keyword_settings' => [
        '#type' => 'fieldset',
        '#title' => $this->t('Keyword service settings', [], ['context' => 'DIPAS']),
        '#description' => $this->t('Settings that are directly related to the keyword service.', [], ['context' => 'DIPAS']),

        'enabled' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Enabled', [], ['context' => 'DIPAS']),
          '#default_value' => $this->enabled,
        ],
        'mode' => [
          '#type' => 'select',
          '#title' => $this->t('Keyword mode', [], ['context' => 'DIPAS']),
          '#description' => $this->t('Shall the users just type keywords (manual) or shall a keyword service be used (internal / external)', [], ['context' => 'DIPAS']),
          '#options' => [
            //'manual' => $this->t('manual', [], ['context' => 'DIPAS']), // ToDo not yet implemented to just store keywords given by user without keyword service request
            'internal' => $this->t('internal', [], ['context' => 'DIPAS']),
            'external' => $this->t('external', [], ['context' => 'DIPAS']),
          ],
          '#states' => [
            'visible' => [':input[type="checkbox"][name="settings[KeywordSettings][keyword_settings][enabled]"]' => ['checked' => TRUE]],
          ],
          '#default_value' => $this->mode,
        ],
        'service_url' => [
          '#type' => 'textfield',
          '#title' => $this->t('Keyword service URL', [], ['context' => 'DIPAS']),
          '#description' => $this->t('For mode = external or internal URL of the keyword service provider must be given.', [], ['context' => 'DIPAS']),
          '#states' => [
            'visible' => [
                          ':input[type="checkbox"][name="settings[KeywordSettings][keyword_settings][enabled]"]' => ['checked' => TRUE],
                          ':input[name="settings[KeywordSettings][keyword_settings][mode]"]' => ['value' => 'external'],
                         ],
            'required' => [':input[type="checkbox"][name="settings[KeywordSettings][keyword_settings][mode]"]' => ['value' => 'external']],
          ],
          '#default_value' => $this->service_url,
        ],
        'externalService' => [
          '#type' => 'textfield',
          '#title' => $this->t('External service', [], ['context' => 'DIPAS']),
          '#description' => $this->t('For mode = external selected the service (Leipzig or DBPedia).', [], ['context' => 'DIPAS']),
          '#states' => [
            'visible' => [
                          ':input[type="checkbox"][name="settings[KeywordSettings][keyword_settings][enabled]"]' => ['checked' => TRUE],
                          ':input[name="settings[KeywordSettings][keyword_settings][mode]"]' => ['value' => 'external'],
                         ],
            'required' => [':input[type="checkbox"][name="settings[KeywordSettings][keyword_settings][mode]"]' => ['value' => 'external']],
          ],
          '#default_value' => $this->externalService,
        ],
        'number_of_keywords' => [
          '#type' => 'number',
          '#title' => $this->t('Number of keywords to be requested', [], ['context' => 'DIPAS']),
          '#description' => $this->t('Set the number of keywords to be requested from the service.', [], ['context' => 'DIPAS']),
          '#states' => [
            'visible' => [
                          ':input[type="checkbox"][name="settings[KeywordSettings][keyword_settings][enabled]"]' => ['checked' => TRUE],
                          ':input[name="settings[KeywordSettings][keyword_settings][mode]"]' => [['value' => 'external'],
                              ['value' => 'internal']],
                         ],
            'required' => [':input[type="checkbox"][name="settings[KeywordSettings][keyword_settings][mode]"]' => ['value' => 'external']],
          ],
          '#min' => 1,
          '#max' => 10,
          '#step' => 1,
          '#default_value' => $this->number_of_keywords,
        ],
      ],
    ];
  }


  /**
   * {@inheritdoc}
   */
  public static function getProcessedValues(array $plugin_values, array $form_values) {
      return [
        'enabled' => (bool) $plugin_values['keyword_settings']['enabled'],
        'mode' => $plugin_values['keyword_settings']['mode'],
        'service_url' => $plugin_values['keyword_settings']['service_url'],
        'externalService' => $plugin_values['keyword_settings']['externalService'],
        'number_of_keywords' => (int) $plugin_values['keyword_settings']['number_of_keywords'],
      ];
  }

  /**
   * {@inheritdoc}
   */
  public function onSubmit() {
  }

}
