<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\ResponseKey;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\dipas\Annotation\ResponseKey;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class GetComments.
 *
 * @ResponseKey(
 *   id = "getcomments",
 *   description = @Translation("Fetch the comments on a given contribution node."),
 *   requestMethods = {
 *     "GET",
 *   },
 *   isCacheable = true
 * )
 *
 * @package Drupal\dipas\Plugin\ResponseKey
 */
class GetComments extends ContributionNodeRequestBase {

  use StringTranslationTrait;
  use RetrieveCommentsTrait;
  use DateTimeTrait;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $commentStorage;

  /**
   * @var array
   */
  protected $cacheTags = [];

  /**
   * @var int
   */
  protected $commentCount = 0;

  /**
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * {@inheritdoc}
   */
  protected function setAdditionalDependencies(ContainerInterface $container) {
    $this->dateFormatter = $container->get('date.formatter');
    $this->commentStorage = $this->entityTypeManager->getStorage('comment');
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginResponse() {
    $this->checkRequest();
    $this->cacheTags[] = sprintf('node:comments:%d', $this->currentRequest->attributes->get('id'));
    return [
      'contributionID' => $this->currentRequest->attributes->get('id'),
      'comments' => $this->loadCommentsForEntity($this->getNode()),
      'commentcount' => $this->commentCount,
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getResponseKeyCacheTags() {
    return $this->cacheTags;
  }

  /**
   * {@inheritdoc}
   */
  protected function getDateFormatter() {
    return $this->dateFormatter;
  }

  /**
   * {@inheritdoc}
   */
  protected function getCommentStorage() {
    return $this->commentStorage;
  }

}
