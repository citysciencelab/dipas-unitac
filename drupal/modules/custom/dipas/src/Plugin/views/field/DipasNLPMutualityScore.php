<?php

namespace Drupal\dipas\Plugin\views\field;

/**
 * Field handler to display contribution mutuality scores.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("dipas_nlp_mutuality_score")
 */
class DipasNLPMutualityScore extends DipasScoreFieldBase {

  /**
   * {@inheritdoc}
   */
  protected function getScorePropertyName() {
    return 'mutuality';
  }

}
