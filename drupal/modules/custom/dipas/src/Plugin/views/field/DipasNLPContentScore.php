<?php

namespace Drupal\dipas\Plugin\views\field;

/**
 * Field handler to display contribution content scores.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("dipas_nlp_content_score")
 */
class DipasNLPContentScore extends DipasScoreFieldBase {

  /**
   * {@inheritdoc}
   */
  protected function getScorePropertyName() {
    return 'content';
  }

}
