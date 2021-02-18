<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\SettingsSection;

use Drupal\Component\DependencyInjection\Container;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Class ContributionSettings.
 *
 * @SettingsSection(
 *   id = "ContributionSettings",
 *   title = @Translation("Contributions"),
 *   description = @Translation("Settings related to the contributions users can create."),
 *   weight = 20,
 *   affectedConfig = {
 *     "core.entity_form_display.node.contribution.default",
 *     "core.entity_view_display.node.contribution.full",
 *     "field.field.node.contribution.field_comments"
 *   }
 * )
 *
 * @package Drupal\dipas\Plugin\SettingsSection
 */
class ContributionSettings extends SettingsSectionBase {

  const DIPAS_CONTRIBUTION_STATUS_OPEN = 'open';
  const DIPAS_CONTRIBUTION_STATUS_CLOSED = 'closed';

  /**
   * The instance service of the Masterportal.
   *
   * @var \Drupal\masterportal\Service\InstanceServiceInterface
   */
  protected $instanceService;

  /**
   * {@inheritdoc}
   */
  protected function setAdditionalDependencies(Container $container) {
    $this->instanceService = $container->get('masterportal.instanceservice');
  }

  /**
   * {@inheritdoc}
   */
  public static function getDefaults() {
    return [
      'contribution_status' => static::DIPAS_CONTRIBUTION_STATUS_CLOSED,
      'maximum_character_count_per_contribution' => 1000,
      'contributor_must_be_logged_in' => FALSE,
      'rubrics_use' => TRUE,
      'contributions_must_be_localized' => TRUE,
      'geometry' => ['point'],
      'comments_allowed' => FALSE,
      'comments_maxlength' => 1000,
      'display_existing_comments' => TRUE,
      'rating_allowed' => FALSE,
      'masterportal_instances' => [
        'contributionmap' => 'default',
        'singlecontribution' => [
          'instance' => 'default',
          'other_contributions' => 'hidden',
        ],
        'createcontribution' => 'contribution',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array $form, FormStateInterface $form_state) {
    return [
      'contribution_status' => [
        '#type' => 'value',
        '#value' => $this->contribution_status,
      ],
      'maximum_character_count_per_contribution' => [
        '#type' => 'number',
        '#title' => $this->t('Maximum character count on contributions', [], ['context' => 'DIPAS']),
        '#description' => $this->t('Set the maximum number of characters a user can use in the descriptive text of his contribution.', [], ['context' => 'DIPAS']),
        '#min' => 20,
        '#max' => 3500,
        '#step' => 10,
        '#default_value' => $this->maximum_character_count_per_contribution,
      ],
      'contributor_must_be_logged_in' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Contributor MUST be logged in', [], ['context' => 'DIPAS']),
        '#description' => $this->t('Should only registered and logged-in users be able to contribute? (Function currently unavailable)', [], ['context' => 'DIPAS']),
        '#default_value' => $this->contributor_must_be_logged_in,
        '#attributes' => [
          'disabled' => 'disabled',
        ],
      ],
      'rubrics_use' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Use Rubrics in Contributions', [], ['context' => 'DIPAS']),
        '#description' => $this->t('Should Rubrics be used in Contributions?', [], ['context' => 'DIPAS']),
        '#default_value' => $this->rubrics_use,
      ],
      'contributions_must_be_localized' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Contributions MUST be localized', [], ['context' => 'DIPAS']),
        '#description' => $this->t('Should users be forced to place a localization on a map in order to create contributions?', [], ['context' => 'DIPAS']),
        '#default_value' => $this->contributions_must_be_localized,
      ],
      'geometry' => [
        '#type' => 'checkboxes',
        '#title' => $this->t('Allowed geometry types', [], ['context' => 'DIPAS']),
        '#description' => $this->t('Please select the types of geometry markings the user can create.', [], ['context' => 'DIPAS']),
        '#options' => [
          'point' => $this->t('Point', [], ['context' => 'DIPAS']),
          'linestring' => $this->t('Line', [], ['context' => 'DIPAS']),
          'polygon' => $this->t('Area', [], ['context' => 'DIPAS']),
        ],
        '#default_value' => $this->geometry,
        '#required' => TRUE,
      ],
      'comments_allowed' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Allow comments on contributions', [], ['context' => 'DIPAS']),
        '#description' => $this->t('When activated, users can write comments on contributions created by other users. If de-activated mid-term, existing comments will not be deleted, and will still be displayed. Only the authoring of new comments will be disabled.', [], ['context' => 'DIPAS']),
        '#default_value' => $this->comments_allowed,
      ],
      'comments_maxlength' => [
        '#type' => 'number',
        '#title' => $this->t('Maximum character count on comments', [], ['context' => 'DIPAS']),
        '#description' => $this->t('Set the maximum number of characters a user can use within his/her comments.', [], ['context' => 'DIPAS']),
        '#min' => 20,
        '#max' => 3500,
        '#step' => 10,
        '#default_value' => $this->comments_maxlength,
        '#states' => [
          'visible' => [':input[type="checkbox"][name="settings[ContributionSettings][comments_allowed]"]' => ['checked' => TRUE]],
          'required' => [':input[type="checkbox"][name="settings[ContributionSettings][comments_allowed]"]' => ['checked' => TRUE]],
        ],
        '#attributes' => [
          'style' => 'margin-left: 25px;',
        ],
      ],
      'display_existing_comments' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Keep displaying existing comments', [], ['context' => 'DIPAS']),
        '#description' => $this->t('Should already existing comments still be displayed or should they get hidden?', [], ['context' => 'DIPAS']),
        '#default_value' => $this->display_existing_comments,
        '#states' => [
          'visible' => [
            ':input[type="checkbox"][name="settings[ContributionSettings][comments_allowed]"]' => ['checked' => FALSE],
          ]
        ],
        '#attributes' => [
          'style' => 'margin-left: 25px;',
        ],
      ],
      'rating_allowed' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Allow ratings on contributions', [], ['context' => 'DIPAS']),
        '#description' => $this->t('When activated, users can rate contributions other users have created. If de-activated mid-term, existing ratings will not be deleted. The rating widget will still be displayed, but no new rates can be made.', [], ['context' => 'DIPAS']),
        '#default_value' => $this->rating_allowed,
      ],
      'masterportal_instances' => [
        '#type' => 'fieldset',
        '#title' => $this->t('Masterportal instances', [], ['context' => 'DIPAS']),
        'contributionmap' => [
          '#type' => 'select',
          '#title' => $this->t('Contribution map', [], ['context' => 'DIPAS']),
          '#description' => $this->t(
            'Select the Masterportal instance to display contributions on a map. You can configure the instance settings @here.',
            [
              '@here' => Link::fromTextAndUrl(
                'here',
                Url::fromRoute('masterportal.settings.instances')
              )->toString(),
            ],
            ['context' => 'DIPAS']
          ),
          '#required' => TRUE,
          '#empty_option' => $this->t('Please choose', [], ['context' => 'DIPAS']),
          '#options' => $this->instanceService->getInstanceOptions(['config', 'contribution']),
          '#default_value' => $this->masterportal_instances['contributionmap'],
        ],
        'single_contribution_settings' => [
          '#type' => 'container',
          'instance' => [
            '#type' => 'select',
            '#title' => $this->t('Single contribution display', [], ['context' => 'DIPAS']),
            '#description' => $this->t(
              'Select the Masterportal instance to display single contributions in. You can configure the instance settings @here.',
              [
                '@here' => Link::fromTextAndUrl(
                  'here',
                  Url::fromRoute('masterportal.settings.instances')
                )->toString(),
              ],
              ['context' => 'DIPAS']
            ),
            '#required' => TRUE,
            '#empty_option' => $this->t('Please choose', [], ['context' => 'DIPAS']),
            '#options' => $this->instanceService->getInstanceOptions(['config', 'contribution']),
            '#default_value' => $this->masterportal_instances['singlecontribution']['instance'],
          ],
          'other_contributions' => [
            '#type' => 'radios',
            '#title' => $this->t('Hide other contributions on detail pages?', [], ['context' => 'DIPAS']),
            '#options' => [
              'hidden' => $this->t('Hidden', [], ['context' => 'DIPAS']),
              'displayed' => $this->t('Displayed', [], ['context' => 'DIPAS']),
            ],
            '#default_value' => $this->masterportal_instances['singlecontribution']['other_contributions'],
            '#required' => TRUE,
          ],
        ],
        'createcontribution' => [
          '#type' => 'select',
          '#title' => $this->t('Create Contribution', [], ['context' => 'DIPAS']),
          '#description' => $this->t(
            'Select the Masterportal instance to use for creating contributions. You can configure the instance settings @here.',
            [
              '@here' => Link::fromTextAndUrl(
                'here',
                Url::fromRoute('masterportal.settings.instances')
              )->toString(),
            ],
            ['context' => 'DIPAS']
          ),
          '#required' => TRUE,
          '#empty_option' => $this->t('Please choose', [], ['context' => 'DIPAS']),
          '#options' => $this->instanceService->getInstanceOptions(['config']),
          '#default_value' => $this->masterportal_instances['createcontribution'],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function getProcessedValues(array $plugin_values, array $form_values) {
    return [
      'contribution_status' => $plugin_values['contribution_status'],
      'maximum_character_count_per_contribution' => (int) $plugin_values['maximum_character_count_per_contribution'],
      'contributor_must_be_logged_in' => (bool) $plugin_values['contributor_must_be_logged_in'],
      'contributions_must_be_localized' => (bool) $plugin_values['contributions_must_be_localized'],
      'rubrics_use' => (bool) $plugin_values['rubrics_use'],
      'geometry' => array_values(array_filter($plugin_values['geometry'])),
      'comments_allowed' => (bool) $plugin_values['comments_allowed'],
      'comments_maxlength' => (int) $plugin_values['comments_maxlength'],
      'display_existing_comments' => (bool) $plugin_values['display_existing_comments'],
      'rating_allowed' => (bool) $plugin_values['rating_allowed'],
      'masterportal_instances' => [
        'contributionmap' => $plugin_values['masterportal_instances']['contributionmap'],
        'singlecontribution' => [
          'instance' => $plugin_values['masterportal_instances']['single_contribution_settings']['instance'],
          'other_contributions' => $plugin_values['masterportal_instances']['single_contribution_settings']['other_contributions'],
        ],
        'createcontribution' => $plugin_values['masterportal_instances']['createcontribution'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function onSubmit() {}

}
