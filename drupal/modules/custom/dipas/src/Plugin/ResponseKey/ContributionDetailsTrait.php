<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\ResponseKey;

trait ContributionDetailsTrait {

  /**
   * {@inheritdoc}
   */
  protected function getNodeType() {
    return 'contribution';
  }

  /**
   * {@inheritdoc}
   */
  protected function getJoins() {
    return [
      [
        'type' => 'LEFT',
        'table' => 'node__field_category',
        'alias' => 'category',
        'condition' => 'base.type = category.bundle AND base.nid = category.entity_id AND base.vid = category.revision_id AND attr.langcode = category.langcode AND category.deleted = 0',
        'fields' => [
          'field_category_target_id' => 'category',
        ],
      ],
      [
        'type' => 'LEFT',
        'table' => 'node__field_rubric',
        'alias' => 'rubric',
        'condition' => 'base.type = rubric.bundle AND base.nid = rubric.entity_id AND base.vid = rubric.revision_id AND attr.langcode = rubric.langcode AND rubric.deleted = 0',
        'fields' => [
          'field_rubric_target_id' => 'rubric',
        ],
      ],
      [
        'type' => 'LEFT',
        'table' => 'comment_field_data',
        'alias' => 'comments',
        'condition' => "comments.entity_type = 'node' AND base.nid = comments.entity_id AND comments.status = 1",
      ],
      [
        'type' => 'LEFT',
        'table' => 'comment_field_data',
        'alias' => 'replies',
        'condition' => "replies.entity_type = 'comment' AND comments.cid = replies.entity_id AND replies.status = 1",
      ],
      [
        'type' => 'LEFT',
        'table' => 'votingapi_vote',
        'alias' => 'upvotes',
        'condition' => "upvotes.type = 'vote' AND upvotes.entity_type = 'node' AND upvotes.entity_id = base.nid AND upvotes.value = 1",
      ],
      [
        'type' => 'LEFT',
        'table' => 'votingapi_vote',
        'alias' => 'downvotes',
        'condition' => "downvotes.type = 'vote' AND downvotes.entity_type = 'node' AND downvotes.entity_id = base.nid AND downvotes.value = -1",
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getGroupBy() {
    return ['category.field_category_target_id', 'rubric.field_rubric_target_id', 'comments.entity_id'];
  }

  /**
   * {@inheritdoc}
   */
  function getExpressions() {
    return [
      'coalesce(count(distinct comments.cid), 0) + coalesce(count(distinct replies.cid), 0)' => 'comments',
      'COUNT(DISTINCT upvotes.id)' => 'upVotes',
      'COUNT(DISTINCT downvotes.id)' => 'downVotes',
      'COUNT(DISTINCT upvotes.id) + COUNT(DISTINCT downvotes.id)' => 'numVotes',
      'ROUND((100 * COUNT(DISTINCT upvotes.id) / (CASE COUNT(DISTINCT upvotes.id) + COUNT(DISTINCT downvotes.id) WHEN 0 THEN 1 ELSE COUNT(DISTINCT upvotes.id) + COUNT(DISTINCT downvotes.id) END)) / 100.0, 4)' => 'rating',
    ];
  }

}
