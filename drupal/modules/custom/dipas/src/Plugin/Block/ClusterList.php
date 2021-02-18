<?php

namespace Drupal\dipas\Plugin\Block;

use Drupal\Core\Url;

/**
 * Provides a block providing an overview on NLP clusters.
 *
 * @Block(
 *   id = "dipas_nlp_clusterlist_block",
 *   admin_label = @Translation("DIPAS NLP clusterlist block"),
 *   category = @Translation("DIPAS"),
 * )
 */
class ClusterList extends NLPBlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      'block' => [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#attributes' => [
          'class' => ['dipas_nlp_block', 'dipas_nlp_clusterlist'],
          'data-blocksettings' => 'DipasClusterListBlock',
        ],
        'header' => [
          '#type' => 'html_tag',
          '#tag' => 'div',
          '#attributes' => [
            'class' => ['header'],
          ],
          'category' => $this->getTermFilterOptions('categories', 'Category', 'field_category'),
          'rubric' => $this->getTermFilterOptions('rubrics', 'Rubric', 'field_rubric'),
        ],
        'body' => [
          '#type' => 'html_tag',
          '#tag' => 'div',
          '#attributes' => [
            'class' => ['body'],
          ],
          '#value' => $this->t('JavaScript must be enabled in order to use this list'),
        ],
      ],
      '#attached' => [
        'library' => ['dipas/nlpblock'],
        'drupalSettings' => [
          'DipasClusterListBlock' => [
            'callbackUrl' => Url::fromRoute('dipas.clusterlist', [], ['absolute' => TRUE])->toString(),
            'dataType' => 'list',
          ],
        ],
      ],
    ];
  }

}
