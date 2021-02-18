<?php

namespace Drupal\dipas\Plugin\views\field;

/**
 * Field handler to display contribution response scores.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("dipas_nlp_response_score")
 */
class DipasNLPResponseScore extends DipasScoreFieldBase {

  /**
   * {@inheritdoc}
   */
  protected function getScorePropertyName() {
    return 'response';
  }

}
