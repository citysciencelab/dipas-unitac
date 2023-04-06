<?php

namespace Drupal\dipas\Controller;

use Drupal\Core\Controller\ControllerBase;

class NLPAnalysis extends ControllerBase {

  /**
   * Page callback function for routing.yml
   */
  public static function pageCallback() {
    $config = \Drupal::service('dipas.config');
    $nlp_enabled = $config->get('NLPSettings.enabled');
    $cluster_enabled = $nlp_enabled && $config->get('NLPSettings.enable_clustering');
    $wordcloud_enabled = $nlp_enabled && $config->get('NLPSettings.enable_wordcloud');

    return [
      '#theme' => 'dipas_nlp_analysis',
      '#nlp_enabled' => $nlp_enabled,
      '#nlp_cluster_enabled' => $cluster_enabled,
      '#nlp_wordcloud_enabled' => $wordcloud_enabled,
      '#attached' => [
        'library' => ['dipas/nlpanalysis'],
      ],
    ];
  }

}
