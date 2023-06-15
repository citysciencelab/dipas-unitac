<?php

namespace Drupal\dipas\Plugin\CockpitDataResponse;

use Drupal\dipas\Annotation\CockpitDataResponse;

/**
 * Class Contributions.
 *
 * @CockpitDataResponse(
 *   id = "contributions",
 *   description = @Translation("Lists all contributions metadata of all domains"),
 *   requestMethods = {
 *       "GET"  ,
 *   },
 *   isCacheable = true,
 *   maxAge = 5
 * )
 *
 * @package Drupal\dipas\Plugin\CockpitDataResponse
 */
class Contributions extends CockpitDataResponseBase {

  /**
   * {@inheritdoc}
   */
  protected function getResponseKeyCacheTags() {
    $cachetags = ['CockpitDataResponse', 'CockpitDataResponseContributions'];

    return $cachetags;
  }

  /**
   * {@inheritdoc}
   */
  protected function getPluginResponse() {
    $query = $this->database->select('node', 'base')
      ->condition('base.type', 'contribution', '=')
      ->condition('attr.status', '1', '=');

    $query->addJoin(
      'LEFT',
      'node_field_data',
      'attr',
      'base.type = attr.type AND base.nid = attr.nid AND base.vid = attr.vid'
    );

    $query->addExpression("to_char(to_timestamp(attr.created), 'YYYY')", 'year');
    $query->addExpression('COUNT(base.nid)', 'contributions');

    $query->groupBy('year');
    $query->orderBy('year', 'ASC');

    $data = $query->execute()->fetchAll();

    return [
      'chart_data' => [
        'total' => array_sum(array_map(function ($row) { return $row->contributions; }, $data)),
        'max' => max(array_map(function ($row) { return $row->contributions; }, $data)),
        'by_year' => array_combine(
          array_map(function ($row) { return $row->year; }, $data),
          array_map(function ($row) { return $row->contributions; }, $data)
        ),
      ],
    ];
  }

}
