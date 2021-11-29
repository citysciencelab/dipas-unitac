<?php

/**
 * Implements hook_views_data_alter().
 */
function dipas_views_data_alter(array &$data) {
  $data['node']['dipas_nlp_relevance_score'] = [
    'title' => t('Contribution relevance score'),
    'field' => [
      'title' => t('Contribution relevance score'),
      'help' => t('Displays the relevance score for a contribution that was determined by the NLP service.'),
      'id' => 'dipas_nlp_relevance_score',
    ],
  ];

  $data['node']['dipas_nlp_response_score'] = [
    'title' => t('Contribution response score'),
    'field' => [
      'title' => t('Contribution response score'),
      'help' => t('Displays the response score for a contribution that was determined by the NLP service.'),
      'id' => 'dipas_nlp_response_score',
    ],
  ];

  $data['node']['dipas_nlp_content_score'] = [
    'title' => t('Contribution content score'),
    'field' => [
      'title' => t('Contribution content score'),
      'help' => t('Displays the content score for a contribution that was determined by the NLP service.'),
      'id' => 'dipas_nlp_content_score',
    ],
  ];

  $data['node']['dipas_nlp_mutuality_score'] = [
    'title' => t('Contribution mutuality score'),
    'field' => [
      'title' => t('Contribution mutuality score'),
      'help' => t('Displays the mutuality score for a contribution that was determined by the NLP service.'),
      'id' => 'dipas_nlp_mutuality_score',
    ],
  ];

  $data['node']['dipas_nlp_cluster_filter'] = [
    'title' => t('Cluster filter'),
    'filter' => [
      'title' => t('Cluster filter'),
      'help' => t('Provides a custom filter for contribution nodes by their respective cluster.'),
      'field' => 'nid',
      'id' => 'dipas_nlp_cluster_filter',
    ],
  ];
}
