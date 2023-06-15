<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html
 *   GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\ResponseKey;

use Drupal\comment\Entity\Comment;
use Drupal\Component\Utility\Xss;
use Drupal\dipas\Exception\MalformedRequestException;
use Drupal\dipas\Exception\StatusException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AddComment.
 *
 * @ResponseKey(
 *   id = "addcomment",
 *   description = @Translation("Append a comment to a given entity."),
 *   requestMethods = {
 *     "POST",
 *   },
 *   isCacheable = false,
 *   shieldRequest = true
 * )
 *
 * @package Drupal\dipas\Plugin\ResponseKey
 */
class AddComment extends GetComments {

  /**
   * Drupal's cache tags invalidation service.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheTagsInvalidator;

  /**
   * {@inheritdoc}
   */
  protected function setAdditionalDependencies(ContainerInterface $container) {
    parent::setAdditionalDependencies($container);
    $this->cacheTagsInvalidator = $container->get('cache_tags.invalidator');
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginResponse() {
    $sanitizedFields = $this->getSanitizedCommentFields();
    $this->checkRequest($sanitizedFields['rootEntityID']);

    // First, determine what bundle should get commented on.
    $commentedNode = $this->getNode($sanitizedFields['rootEntityID']);
    $dipasSettingToCheck = $commentedNode->bundle() === 'contribution'
      ? 'ContributionSettings.comments_allowed'
      : 'ProjectSchedule.allow_conception_comments';

    if ($this->dipasConfig->get($dipasSettingToCheck)) {
      $commentEntity = Comment::create([
        'entity_type' => $sanitizedFields['commentedEntityType'],
        'entity_id' => $sanitizedFields['commentedEntityID'],
        'field_name' => $sanitizedFields['commentedEntityType'] === 'node' ? 'field_comments' : 'field_replies',
        'uid' => 0,
        'status' => 1,
        'comment_type' => $sanitizedFields['commentedEntityType'] === 'node' ? 'default' : 'reply',
        'subject' => trim($sanitizedFields['subject']),
        'field_comment' => trim($sanitizedFields['comment']),
      ]);
      $commentEntity->save();
      $this->cacheTagsInvalidator->invalidateTags([sprintf('node:comments:%d', $sanitizedFields['rootEntityID'])]);
      return [
        'contributionID' => $sanitizedFields['rootEntityID'],
        'comments' => $this->loadCommentsForEntity($this->getNode($sanitizedFields['rootEntityID'])),
        'commentcount' => $this->commentCount,
        'insertedCommentID' => $commentEntity->id(),
      ];
    }
    else {
      throw new StatusException("The platform is closed for new comments.", 403);
    }
  }

  /**
   * Sanitizes posted data and checks for required fields.
   *
   * @return array
   *   The sanitized data.
   *
   * @throws \Drupal\dipas\Exception\MalformedRequestException
   *   When required fields are missing, this exception is thrown.
   */
  protected function getSanitizedCommentFields() {
    $requiredFields = [
      'rootEntityID',
      'commentedEntityType',
      'commentedEntityID',
      'subject',
      'comment',
    ];
    $requiredFields = array_combine($requiredFields, array_fill(0, count($requiredFields), TRUE));
    if (!($postedFields = json_decode($this->currentRequest->getContent()))) {
      throw new MalformedRequestException('Request data could not be decoded!', 400);
    }
    else {
      $sanitized = [];
      foreach ($postedFields as $key => $value) {
        if (isset($requiredFields[$key])) {
          $requiredFields[$key] = FALSE;
        }
        $sanitized[$key] = Xss::filter($value);
      }
      if (!empty(array_keys(array_filter($requiredFields)))) {
        throw new MalformedRequestException("Missing data!", 400);
      }
      elseif (count($sanitized) > count($requiredFields)) {
        // Throw an exception if more fields are submitted than used (likely spam).
        throw new MalformedRequestException("The server rejected the request!", 400);
      }
      return $sanitized;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getResponseKeyCacheTags() {
    return [];
  }

}
