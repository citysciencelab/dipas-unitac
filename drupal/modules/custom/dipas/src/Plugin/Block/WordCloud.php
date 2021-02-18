<?php

namespace Drupal\dipas\Plugin\Block;

use Drupal\Core\Url;

/**
 * Provides a block displaying a wordcloud matching filter criteria.
 *
 * @Block(
 *   id = "dipas_nlp_wordcloud_block",
 *   admin_label = @Translation("DIPAS NLP wordcloud block"),
 *   category = @Translation("DIPAS"),
 * )
 */
class WordCloud extends NLPBlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      'block' => [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#attributes' => [
          'class' => ['dipas_nlp_block', 'dipas_nlp_wordcloud'],
          'data-blocksettings' => 'DipasWordcloudBlock',
        ],
        'header' => [
          '#type' => 'html_tag',
          '#tag' => 'div',
          '#attributes' => [
            'class' => ['header'],
          ],
          'category' => $this->getTermFilterOptions('categories', 'Category', 'field_category'),
          'rubric' => $this->getTermFilterOptions('rubrics', 'Rubric', 'field_rubric'),
          'cluster' => $this->getClusterFilterOptions(),
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
          'DipasWordcloudBlock' => [
            'callbackUrl' => Url::fromRoute('dipas.wordcloud', [], ['absolute' => TRUE])->toString(),
            'dataType' => 'image',
          ],
        ],
      ],
    ];
  }

}
