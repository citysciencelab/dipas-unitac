<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\ResponseKey;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\comment\CommentManagerInterface;

trait RetrieveCommentsTrait {

   /**
   * Returns stored comments recursively.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity for which to fetch comments.
   *
   * @param string $format_type
   *   The type of format for the return value
   *   possible values: dipas, pds
   *   default: dipas
   *
   * @return array
   *   The comments for the entity.
   */
  protected function loadCommentsForEntity(ContentEntityInterface $entity, $format_type = 'dipas') {
    $commentList = [];
    $commentsField = $entity->getEntityTypeId() === 'node' ? 'field_comments' : 'field_replies';
    $commentStorage = $this->getCommentStorage();
    $comments = $commentStorage->loadThread($entity, $commentsField, CommentManagerInterface::COMMENT_MODE_FLAT);
    foreach ($comments as $comment) {
      /* @var \Drupal\comment\CommentInterface $comment */
      $this->cacheTags[] = sprintf('comment:%d', $comment->id());
      $this->commentCount++;
      $subject = ($subject = $comment->get('subject')->first()) ? $subject->getString() : '';
      $commentListEntry = $this->formatCommentsForOutput($comment, $subject, $format_type);
      $commentList[] = $commentListEntry;
    }
    return $commentList;
  }

  /**
   * Returns number of stored comments recursively.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity for which to fetch comments.
   *
   * @return int
   *   The number comments (and replies) for the entity.
   */
  protected function countCommentsForEntity(ContentEntityInterface $entity) {
    $commentsField = $entity->getEntityTypeId() === 'node' ? 'field_comments' : 'field_replies';
    $commentStorage = $this->getCommentStorage();
    $comments = $commentStorage->loadThread($entity, $commentsField, CommentManagerInterface::COMMENT_MODE_FLAT);
    $comment_count = count($comments);

    foreach ($comments as $comment) {
      $comment_count += $this->countCommentsForEntity($comment);
    }

    return $comment_count;
  }

  /**
   * returns array of comment information, formated as expected for dipas REST API
   */
  protected function formatCommentsForOutput($comment, $subject, $format_type) {
    $return_value = [];

    if ($format_type === 'dipas') {
      $return_value = [
        'cid' => $comment->id(),
        'subject' => $subject !== $this->t('(No subject)', [], ['context' => 'DIPAS'])->__toString()
          ? html_entity_decode($subject, ENT_QUOTES, 'UTF-8')
          : '',
        'comment' => html_entity_decode($comment->get('field_comment')->first()->getString(), ENT_QUOTES, 'UTF-8'),
        'created' => $this->convertTimestampToUTCDateTimeString($comment->getCreatedTime(), FALSE),
        'replies' => $this->loadCommentsForEntity($comment, $format_type),
      ];
    }
    else if ($format_type === 'pds') {
      $return_value = [
        'id' => $comment->id(),
        'dateCreated' => $this->convertTimestampToUTCDateTimeString($comment->getCreatedTime(), FALSE),
        'title' => $subject !== $this->t('(No subject)', [], ['context' => 'DIPAS'])->__toString() ? $subject : '',
        'commentContent' => $comment->get('field_comment')->first()->getString(),
        'commentedBy' => $this->loadCommentsForEntity($comment, $format_type),
      ];
    }

    return $return_value;
  }

  /**
   * Returns the commentStorage.
   *
   * @return \Drupal\Core\Entity\EntityStorageInterface
   */
  abstract protected function getCommentStorage();
}
