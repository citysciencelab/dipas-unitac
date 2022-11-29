<?php

namespace Drupal\dipas\Plugin\CockpitDataResponse;

use Drupal\dipas\Annotation\CockpitDataResponse;

/**
 * Class Contributions.
 *
 * @CockpitDataResponse(
 *   id = "comments",
 *   description = @Translation("Lists all comments metadata of all domains"),
 *   requestMethods = {
 *       "GET"  ,
 *   },
 *   isCacheable = true,
 *   maxAge = 5
 * )
 *
 * @package Drupal\dipas\Plugin\CockpitDataResponse
 */
class Comments extends CockpitDataResponseBase {

  /**
   * {@inheritdoc}
   */
  protected function getResponseKeyCacheTags() {
    $cachetags = ['CockpitDataResponse', 'CockpitDataResponseComments'];

    return $cachetags;
  }

  /**
   * {@inheritdoc}
   */
  protected function getPluginResponse() {
    $query = $this->database->select('comment', 'base')
      ->condition('attr.status', '1', '=');

    $query->addJoin(
      'LEFT',
      'comment_field_data',
      'attr',
      'base.cid = attr.cid'
    );

    $query->addExpression("to_char(to_timestamp(attr.created), 'YYYY')", 'year');
    $query->addExpression('COUNT(base.cid)', 'comments');

    $query->groupBy('year');
    $query->orderBy('year', 'ASC');

    $data = $query->execute()->fetchAll();

    return [
      'chart_data' => [
        'total' => array_sum(array_map(function ($row) { return $row->comments; }, $data)),
        'max' => max(array_map(function ($row) { return $row->comments; }, $data)),
        'by_year' => array_combine(
          array_map(function ($row) { return $row->year; }, $data),
          array_map(function ($row) { return $row->comments; }, $data)
        ),
      ],
    ];
  }

}
