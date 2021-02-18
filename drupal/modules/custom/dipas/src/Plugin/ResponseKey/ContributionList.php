<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\ResponseKey;

/**
 * Class ContributionList.
 *
 * @ResponseKey(
 *   id = "contributionlist",
 *   description = @Translation("Returns a list of contributions currently contained in the database within given limits and filters."),
 *   requestMethods = {
 *     "GET",
 *   },
 *   isCacheable = true
 * )
 *
 * @package Drupal\dipas\Plugin\ResponseKey
 */
class ContributionList extends PagedNodeListingBase {

  use ContributionDetailsTrait;

  /**
   * {@inheritdoc}
   */
  protected function getConditions() {
    $filters = [];
    if ($this->currentRequest->query->has('category')) {
      $filters[] = [
        'field' => 'category.field_category_target_id',
        'value' => explode(',', $this->currentRequest->query->get('category')),
        'operator' => 'IN'
      ];
    }
    if ($this->currentRequest->query->has('rubric')) {
      $filters[] = [
        'field' => 'rubric.field_rubric_target_id',
        'value' => explode(',', $this->currentRequest->query->get('rubric')),
        'operator' => 'IN'
      ];
    }
    return $filters;
  }

  /**
   * {@inheritdoc}
   */
  protected function getSortingField() {
    if (
      $this->currentRequest->query->has('sort') &&
      ($field = $this->currentRequest->query->get('sort')) &&
      in_array(strtolower($field), ['created', 'rating', 'comments'])
    ) {
      return strtolower($field);
    }
    return 'created';
  }

}
