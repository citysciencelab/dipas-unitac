<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\ResponseKey;

use Drupal\dipas\Exception\MalformedRequestException;
use Drupal\dipas\Exception\StatusException;
use Drupal\votingapi\Entity\Vote;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Rate.
 *
 * @ResponseKey(
 *   id = "rate",
 *   description = @Translation("Rate a given entity."),
 *   requestMethods = {
 *     "POST",
 *   },
 *   isCacheable = false,
 *   shieldRequest = true
 * )
 *
 * @package Drupal\dipas\Plugin\ResponseKey
 */
class Rate extends ResponseKeyBase {

  use RetrieveRatingTrait;
  use DateTimeTrait;

  /**
   * Drupal's cache tags invalidation service.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheTagInvalidator;

  /**
   * @var \Drupal\dipas\Service\DipasCookieInterface
   */
  protected $dipasCookie;

  /**
   * @var array
   */
  protected $postedFields;

  /**
   * @var \Drupal\Core\Entity\ContentEntityInterface
   */
  protected $entityToRate;

  /**
   * @var int
   */
  protected $rating;

  /**
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * {@inheritdoc}
   */
  protected function setAdditionalDependencies(ContainerInterface $container) {
    $this->cacheTagInvalidator = $container->get('cache_tags.invalidator');
    $this->dipasCookie = $container->get('dipas.cookie');
    $this->dateFormatter = $container->get('date.formatter');
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityTypeId() {
    return $this->entityToRate->getEntityTypeId();
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityId() {
    return $this->entityToRate->id();
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginResponse() {
    $this->checkRequest();
    $this->castVote();
    $this->cacheTagInvalidator->invalidateTags([sprintf('%s:%s', $this->entityToRate->getEntityTypeId(), $this->entityToRate->id())]);
    return [
      'results' => $this->getRating(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCookies() {
    $cookieData = $this->dipasCookie->getCookieData();
    if (!isset($cookieData->votes)) {
      $cookieData->votes = (object) [];
    }
    if (!isset($cookieData->votes->{$this->entityToRate->getEntityTypeId()})) {
      $cookieData->votes->{$this->entityToRate->getEntityTypeId()} = [];
    }
    $cookieData->votes->{$this->entityToRate->getEntityTypeId()}[] = $this->entityToRate->id();
    $this->dipasCookie->setCookieData($cookieData);
    return [$this->dipasCookie->getCookie()];
  }

  /**
   * Checks, if the request is legitimate. Throws exception when not.
   */
  protected function checkRequest() {
    if (!$this->dipasConfig->get('ContributionSettings/rating_allowed')) {
      throw new StatusException('Ratings are closed.', 403);
    }

    if (!$this->dipasCookie->hasCookiesEnabled()) {
      throw new StatusException('Cookies must be accepted in order to vote!', 400);
    }

    $postedFields = json_decode($this->currentRequest->getContent());
    if (!$postedFields) {
      throw new MalformedRequestException('Request data could not be decoded!', 400);
    }
    $this->postedFields = (array) $postedFields;

    $entityType = isset($this->postedFields['entity_type']) ? $this->postedFields['entity_type'] : 'node';
    $storage = $this->entityTypeManager->getStorage($entityType);
    $id = $this->currentRequest->attributes->get('id');
    if (!is_numeric($id) || !($entity = $storage->load($id))) {
      throw new MalformedRequestException('The id given is invalid!', 400);
    }
    $this->entityToRate = $entity;

    $cookieData = $this->dipasCookie->getCookieData();
    if (
      isset($cookieData->votes->{$this->entityToRate->getEntityTypeId()}) &&
      in_array($this->entityToRate->id(), $cookieData->votes->{$this->entityToRate->getEntityTypeId()})
    ) {
      throw new StatusException('A vote for the given entity has already been casted!', 403);
    }

    if (!isset($this->postedFields['rating']) || !is_numeric($this->postedFields['rating'])) {
      throw new MalformedRequestException('Missing data!', 400);
    }
    switch (true) {
      case (int) $this->postedFields['rating'] > 0:
        $this->rating = 1;
        break;
      case (int) $this->postedFields['rating'] < 0:
        $this->rating = -1;
        break;
    }
  }

  /**
   * Cast a vote on the entity provided.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function castVote() {
    $vote = Vote::create(['type' => 'vote']);
    $vote->setVotedEntityType($this->entityToRate->getEntityTypeId());
    $vote->setVotedEntityId($this->entityToRate->id());
    $vote->setValueType('points');
    $vote->setValue($this->rating);
    $vote->setOwnerId(0);
    $vote->save();
  }

  /**
   * {@inheritdoc}
   */
  protected function getResponseKeyCacheTags() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  protected function getDateFormatter() {
    return $this->dateFormatter;
  }
}
