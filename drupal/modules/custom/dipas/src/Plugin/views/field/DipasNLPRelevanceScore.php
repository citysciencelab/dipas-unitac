<?php

namespace Drupal\dipas\Plugin\views\field;

/**
 * Field handler to display contribution relevance scores.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("dipas_nlp_relevance_score")
 */
class DipasNLPRelevanceScore extends DipasScoreFieldBase {

  /**
   * {@inheritdoc}
   */
  protected function getScorePropertyName() {
    return 'relevance';
  }

}
