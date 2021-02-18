<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\PDSResponse;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\comment\CommentManagerInterface;

trait EnrichPDSCommentsTrait {

   /**
   * Enriches the comments with information known here like node_id and prev_comment_id
   *
   * @param array $comments
   *   The array of comments for this contribution.
   *
   * @param int $prev_comment_id
   *   The comment id of the previous comment (if there is some).
   *
   * @param int $node_id
   *   The node id the comment is related to.
   *
   * @return array
   *   The array of comments (and replies) given for the entity, enriched by some attributes.
   */
   protected function enrichComments(array $comments, int $prev_comment_id, int $node_id){

    foreach ($comments as &$comment) {
      $comment['commentOnContribution'] = sprintf('%d', $node_id);

      if ($prev_comment_id !== 0) {
        $comment['commentOnComment'] = $prev_comment_id;
      }

      if ($comment['commentedBy']) {
        $comment['commentedBy'] = $this->enrichComments($comment['commentedBy'], $comment['id'], $node_id);
      }
    }

    return $comments;
  }
}
