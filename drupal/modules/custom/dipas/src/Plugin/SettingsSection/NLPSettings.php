<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\SettingsSection;

use Drupal\Component\DependencyInjection\Container;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class NLPSettings.
 *
 * @SettingsSection(
 *   id = "NLPSettings",
 *   title = @Translation("NLP settings"),
 *   description = @Translation("Settings for the NLP service. A service providing extended statistical information on the contributions."),
 *   weight = 70,
 *   affectedConfig = {},
 *   permissionRequired = "administer nlp services"
 * )
 *
 * @package Drupal\dipas\Plugin\SettingsSection
 */
class NLPSettings extends SettingsSectionBase {

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
      'enable_score_service' => FALSE, // enable the usage of score service
      'score_service_basis_url' => '',
      'enable_score_service_content_score' => 'off',
      'enable_score_service_response_score' => 'off',
      'enable_score_service_mutuality_score' => 'off',
      'enable_score_service_relevance_score' => 'off',
      'enable_score_service_sentiment_score' => 'off',
      'score_stoplist' => '',
      'enable_clustering' => FALSE,
      'clustering_service_basis_url' => '',
      'cluster_count' => 3,
      'cluster_stoplist' => '',
      'enable_summary' => FALSE,
      'summary_service_basis_url' => '',
      'enable_wordcloud' => FALSE,
      'wordcloud_service_basis_url' => '',
      'wordcloud_count' => 3,
      'wordcloud_stoplist' => '',
      'wordcloud_dictionary' => 'off',
      'enable_topicmaps' => FALSE,
      'topicmaps_service_basis_url' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array $form, FormStateInterface $form_state) {
    return  [
      'nlp_settings' => [
        '#type' => 'fieldset',
        '#title' => $this->t('NLP service settings', [], ['context' => 'DIPAS']),
        '#description' => $this->t('Settings that are directly related to the NLP service. A service providing extended statistical information on the contributions.', [], ['context' => 'DIPAS']),

        'enabled' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Enabled', [], ['context' => 'DIPAS']),
          '#default_value' => $this->enabled,
        ],
        'enable_score_service' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Enable the score service', [], ['context' => 'DIPAS']),
          '#states' => [
            'visible' => [
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enabled]"]' => ['checked' => TRUE],
          ],
        ],
        '#default_value' => $this->enable_score_service,
        ],
        'score_service_basis_url' => [
          '#type' => 'textfield',
          '#title' => $this->t('Score service basic URL', [], ['context' => 'DIPAS']),
          '#description' => $this->t('The URL of the score service.', [], ['context' => 'DIPAS']),
          '#states' => [
            'visible' => [
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enable_score_service]"]' => ['checked' => TRUE],
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enabled]"]' => ['checked' => TRUE],
            ],
            'required' => [
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enable_score_service]"]' => ['checked' => TRUE],
            ],
          ],
          '#default_value' => $this->score_service_basis_url,
        ],
        'enable_score_service_content_score' => [
          '#type' => 'select',
          '#title' => $this->t('Enable the content score', [], ['context' => 'DIPAS']),
          '#description' => $this->t('Content score can be OFF, normalized (returns 0-100) or absolute.', [], ['context' => 'DIPAS']),
          '#options' => [
            'off' => $this->t('OFF', [], ['context' => 'DIPAS']),
            'normalized' => $this->t('normalized', [], ['context' => 'DIPAS']),
            'absolute' => $this->t('absolute', [], ['context' => 'DIPAS']),
          ],
          '#states' => [
            'visible' => [
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enable_score_service]"]' => ['checked' => TRUE],
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enabled]"]' => ['checked' => TRUE],
            ],
          ],
          '#default_value' => $this->enable_score_service_content_score,
        ],
        'enable_score_service_response_score' => [
          '#type' => 'select',
          '#title' => $this->t('Enable the response score', [], ['context' => 'DIPAS']),
          '#description' => $this->t('Response score can be OFF, normalized (returns 0-100) or absolute.', [], ['context' => 'DIPAS']),
          '#options' => [
            'off' => $this->t('OFF', [], ['context' => 'DIPAS']),
            'normalized' => $this->t('normalized', [], ['context' => 'DIPAS']),
            'absolute' => $this->t('absolute', [], ['context' => 'DIPAS']),
          ],
          '#states' => [
            'visible' => [
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enable_score_service]"]' => ['checked' => TRUE],
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enabled]"]' => ['checked' => TRUE],
            ],
          ],
          '#default_value' => $this->enable_score_service_response_score,
        ],
        'enable_score_service_mutuality_score' => [
          '#type' => 'select',
          '#title' => $this->t('Enable the mutuality score', [], ['context' => 'DIPAS']),
          '#description' => $this->t('Mutuality score can be OFF, normalized (returns 0-100) or absolute.', [], ['context' => 'DIPAS']),
          '#options' => [
            'off' => $this->t('OFF', [], ['context' => 'DIPAS']),
            'normalized' => $this->t('normalized', [], ['context' => 'DIPAS']),
            'absolute' => $this->t('absolute', [], ['context' => 'DIPAS']),
          ],
          '#states' => [
            'visible' => [
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enable_score_service]"]' => ['checked' => TRUE],
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enabled]"]' => ['checked' => TRUE],
            ],
          ],
        '#default_value' => $this->enable_score_service_mutuality_score,
        ],
        'enable_score_service_relevance_score' => [
          '#type' => 'select',
          '#title' => $this->t('Enable the relevance score', [], ['context' => 'DIPAS']),
          '#description' => $this->t('Relevance score can be OFF, normalized (returns 0-100) or absolute.', [], ['context' => 'DIPAS']),
          '#options' => [
            'off' => $this->t('OFF', [], ['context' => 'DIPAS']),
            'normalized' => $this->t('normalized', [], ['context' => 'DIPAS']),
            'absolute' => $this->t('absolute', [], ['context' => 'DIPAS']),
          ],
          '#states' => [
            'visible' => [
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enable_score_service]"]' => ['checked' => TRUE],
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enabled]"]' => ['checked' => TRUE],
            ],
          ],
        '#default_value' => $this->enable_score_service_relevance_score,
        ],
        'enable_score_service_sentiment_score' => [
          '#type' => 'select',
          '#title' => $this->t('Enable the sentiment score', [], ['context' => 'DIPAS']),
          '#description' => $this->t('Sentiment score can be OFF, normalized (returns 0-100) or absolute.', [], ['context' => 'DIPAS']),
          '#options' => [
            'off' => $this->t('OFF', [], ['context' => 'DIPAS']),
            'normalized' => $this->t('normalized', [], ['context' => 'DIPAS']),
            'absolute' => $this->t('absolute', [], ['context' => 'DIPAS']),
          ],
          '#states' => [
            'visible' => [
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enable_score_service]"]' => ['checked' => TRUE],
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enabled]"]' => ['checked' => TRUE],
            ],
          ],
        '#default_value' => $this->enable_score_service_sentiment_score,
        ],
        'score_stoplist' => [
          '#type' => 'textfield',
          '#title' => $this->t('Score blacklist', [], ['context' => 'DIPAS']),
          '#description' => $this->t('Add specific stopwords, which are not indexed seperated by a comma', [], ['context' => 'DIPAS']),
          '#states' => [
            'visible' => [
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enable_score_service]"]' => ['checked' => TRUE],
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enabled]"]' => ['checked' => TRUE],
            ],
          ],
          '#default_value' => $this->score_stoplist,
        ],
        'hr2' => [
          '#type' => 'html_tag',
          '#tag' => 'hr',
          '#states' => [
            'visible' => [
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enable_wordcloud]"]' => ['checked' => TRUE],
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enabled]"]' => ['checked' => TRUE],
            ],
          ]
        ],
        'enable_clustering' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Enable the clustering service', [], ['context' => 'DIPAS']),
          '#states' => [
            'visible' => [
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enabled]"]' => ['checked' => TRUE],
            ],
          ],
          '#default_value' => $this->enable_clustering,
        ],
        'clustering_service_basis_url' => [
          '#type' => 'textfield',
          '#title' => $this->t('Clustering service basic URL', [], ['context' => 'DIPAS']),
          '#description' => $this->t('The URL of the clustering service.', [], ['context' => 'DIPAS']),
          '#states' => [
            'visible' => [
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enable_clustering]"]' => ['checked' => TRUE],
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enabled]"]' => ['checked' => TRUE],
            ],
            'required' => [
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enable_clustering]"]' => ['checked' => TRUE],
            ],
          ],
          '#default_value' => $this->clustering_service_basis_url,
        ],
        'cluster_count' => [
          '#type' => 'number',
          '#step' => 1,
          '#min' => 3,
          '#max' => 10,
          '#title' => $this->t('Number of clusters to be built.', [], ['context' => 'DIPAS']),
          '#states' => [
            'visible' => [
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enable_clustering]"]' => ['checked' => TRUE],
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enabled]"]' => ['checked' => TRUE],
            ],
            'required' => [
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enable_clustering]"]' => ['checked' => TRUE],
            ],
          ],
          '#default_value' => $this->cluster_count,
        ],
        'cluster_stoplist' => [
          '#type' => 'textfield',
          '#title' => $this->t('Clustering blacklist', [], ['context' => 'DIPAS']),
          '#description' => $this->t('Add specific stopwords, which are not indexed seperated by a comma', [], ['context' => 'DIPAS']),
          '#states' => [
            'visible' => [
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enable_clustering]"]' => ['checked' => TRUE],
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enabled]"]' => ['checked' => TRUE],
            ],
          ],
          '#default_value' => $this->cluster_stoplist,
        ],
        'hr3' => [
          '#type' => 'html_tag',
          '#tag' => 'hr',
          '#states' => [
            'visible' => [
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enable_score_service]"]' => ['checked' => TRUE],
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enabled]"]' => ['checked' => TRUE],
            ],
          ]
        ],
        'enable_summary' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Enable the summary service', [], ['context' => 'DIPAS']),
          '#states' => [
            'visible' => [
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enabled]"]' => ['checked' => TRUE],
            ],
          ],
        '#default_value' => $this->enable_summary,
        ],
        'summary_service_basis_url' => [
          '#type' => 'textfield',
          '#title' => $this->t('Summary service basic URL', [], ['context' => 'DIPAS']),
          '#description' => $this->t('The URL of the summary service.', [], ['context' => 'DIPAS']),
          '#states' => [
            'visible' => [
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enable_summary]"]' => ['checked' => TRUE],
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enabled]"]' => ['checked' => TRUE],
            ],

            'required' => [
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enable_summary]"]' => ['checked' => TRUE],
            ],
          ],
        '#default_value' => $this->summary_service_basis_url,
        ],
        'hr4' => [
          '#type' => 'html_tag',
          '#tag' => 'hr',
          '#states' => [
            'visible' => [
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enable_summary]"]' => ['checked' => TRUE],
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enabled]"]' => ['checked' => TRUE],
            ],
          ]
        ],
        'enable_wordcloud' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Enable the wordcloud service', [], ['context' => 'DIPAS']),
          '#states' => [
            'visible' => [
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enabled]"]' => ['checked' => TRUE],
            ],
          ],
          '#default_value' => $this->enable_wordcloud,
        ],
        'wordcloud_service_basis_url' => [
          '#type' => 'textfield',
          '#title' => $this->t('Wordcloud service basic URL', [], ['context' => 'DIPAS']),
          '#description' => $this->t('The URL of the wordcloud service.', [], ['context' => 'DIPAS']),
          '#states' => [
            'visible' => [
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enable_wordcloud]"]' => ['checked' => TRUE],
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enabled]"]' => ['checked' => TRUE],
            ],
            'required' => [
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enable_wordcloud]"]' => ['checked' => TRUE],
            ],
          ],
          '#default_value' => $this->wordcloud_service_basis_url,
        ],
        'wordcloud_count' => [
          '#type' => 'number',
          '#step' => 1,
          '#min' => 0,
          '#max' => 100,
          '#title' => $this->t('Number of max keywords in wordcloud.', [], ['context' => 'DIPAS']),
          '#states' => [
            'visible' => [
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enable_wordcloud]"]' => ['checked' => TRUE],
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enabled]"]' => ['checked' => TRUE],
            ],
            'required' => [
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enable_wordcloud]"]' => ['checked' => TRUE],
            ],
          ],
          '#default_value' => $this->wordcloud_count,
        ],
        'wordcloud_stoplist' => [
          '#type' => 'textfield',
          '#title' => $this->t('Wordcloud blacklist', [], ['context' => 'DIPAS']),
          '#description' => $this->t('Add specific stopwords, which are not indexed seperated by a comma', [], ['context' => 'DIPAS']),
          '#states' => [
            'visible' => [
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enable_wordcloud]"]' => ['checked' => TRUE],
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enabled]"]' => ['checked' => TRUE],
            ],
          ],
          '#default_value' => $this->wordcloud_stoplist,
        ],
        'wordcloud_dictionary' => [
          '#type' => 'select',
          '#title' => $this->t('Enable the dictionary', [], ['context' => 'DIPAS']),
          '#description' => $this->t('The dicionary can be OFF, alphabetic, invers.', [], ['context' => 'DIPAS']),
          '#options' => [
            'off' => $this->t('OFF', [], ['context' => 'DIPAS']),
            'alphabetic' => $this->t('alphabetic', [], ['context' => 'DIPAS']),
            'invers' => $this->t('invers', [], ['context' => 'DIPAS']),
          ],
          '#states' => [
            'visible' => [
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enable_wordcloud]"]' => ['checked' => TRUE],
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enabled]"]' => ['checked' => TRUE],
            ],
          ],
          '#default_value' => $this->wordcloud_dictionary,
        ],
        'hr5' => [
          '#type' => 'html_tag',
          '#tag' => 'hr',
          '#states' => [
            'visible' => [
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enable_clustering]"]' => ['checked' => TRUE],
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enabled]"]' => ['checked' => TRUE],
            ],
          ]
        ],
        'enable_topicmaps' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Enable the topicmaps service', [], ['context' => 'DIPAS']),
          '#states' => [
            'visible' => [
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enabled]"]' => ['checked' => TRUE],
          ],
          ],
          '#default_value' => $this->enable_topicmaps,
        ],
        'topicmaps_service_basis_url' => [
          '#type' => 'textfield',
          '#title' => $this->t('Topicmaps service basic URL', [], ['context' => 'DIPAS']),
          '#description' => $this->t('The URL of the topicmaps service.', [], ['context' => 'DIPAS']),
          '#states' => [
            'visible' => [
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enable_topicmaps]"]' => ['checked' => TRUE],
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enabled]"]' => ['checked' => TRUE],
            ],
            'required' => [
              ':input[type="checkbox"][name="settings[NLPSettings][nlp_settings][enable_topicmaps]"]' => ['checked' => TRUE],
            ],
          ],
          '#default_value' => $this->topicmaps_service_basis_url,
        ],
      ],
    ];
  }


  /**
   * {@inheritdoc}
   */
  public static function getProcessedValues(array $plugin_values, array $form_values) {
      return [
        'enabled' => (bool) $plugin_values['nlp_settings']['enabled'],
        'enable_score_service' => (bool) $plugin_values['nlp_settings']['enable_score_service'],
        'score_service_basis_url' => $plugin_values['nlp_settings']['score_service_basis_url'],
        'enable_score_service_content_score' => $plugin_values['nlp_settings']['enable_score_service_content_score'],
        'enable_score_service_response_score' => $plugin_values['nlp_settings']['enable_score_service_response_score'],
        'enable_score_service_mutuality_score' => $plugin_values['nlp_settings']['enable_score_service_mutuality_score'],
        'enable_score_service_relevance_score' => $plugin_values['nlp_settings']['enable_score_service_relevance_score'],
        'enable_score_service_sentiment_score' => $plugin_values['nlp_settings']['enable_score_service_sentiment_score'],
        'score_stoplist' => $plugin_values['nlp_settings']['score_stoplist'],
        'enable_summary' => (bool) $plugin_values['nlp_settings']['enable_summary'],
        'summary_service_basis_url' => $plugin_values['nlp_settings']['summary_service_basis_url'],
        'enable_wordcloud' => (bool) $plugin_values['nlp_settings']['enable_wordcloud'],
        'wordcloud_service_basis_url' => $plugin_values['nlp_settings']['wordcloud_service_basis_url'],
        'wordcloud_dictionary' => $plugin_values['nlp_settings']['wordcloud_dictionary'],
        'wordcloud_count' => $plugin_values['nlp_settings']['wordcloud_count'],
        'wordcloud_stoplist' => $plugin_values['nlp_settings']['wordcloud_stoplist'],
        'enable_clustering' => (bool) $plugin_values['nlp_settings']['enable_clustering'],
        'clustering_service_basis_url' => $plugin_values['nlp_settings']['clustering_service_basis_url'],
        'cluster_count' => $plugin_values['nlp_settings']['cluster_count'],
        'cluster_stoplist' => $plugin_values['nlp_settings']['cluster_stoplist'],
        'enable_topicmaps' => (bool) $plugin_values['nlp_settings']['enable_topicmaps'],
        'topicmaps_service_basis_url' => $plugin_values['nlp_settings']['topicmaps_service_basis_url'],
      ];
  }

  /**
   * {@inheritdoc}
   */
  public function onSubmit() {
  }

}
