<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\ResponseKey;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ConceptionDetails.
 *
 * @ResponseKey(
 *   id = "conceptiondetails",
 *   description = @Translation("Returns the content for a given conception id."),
 *   requestMethods = {
 *     "GET",
 *   },
 *   isCacheable = true
 * )
 *
 * @package Drupal\dipas\Plugin\ResponseKey
 */
class ConceptionDetails extends ResponseKeyBase {

  use RetrieveRatingTrait;
  use DateTimeTrait;
  use NodeContentTrait {
    getPluginResponse as protected nodeContentResponseData;
  }

  /**
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function setAdditionalDependencies(ContainerInterface $container) {
    $this->dateFormatter = $container->get('date.formatter');
    $this->entityTypeManager = $container->get('entity_type.manager');
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityTypeId() {
    return 'node';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityId() {
    return $this->loadNode()->id();
  }

  /**
   * {@inheritdoc}
   */
  protected function getNodeId() {
    return $this->currentRequest->attributes->get('id');
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginResponse() {
    $responseData = $this->nodeContentResponseData();
    $responseData['rating'] = $this->getRating();
    return $responseData;
  }

  /**
   * Loads and returns a user (singleton).
   *
   * @return \Drupal\user\UserInterface
   *   The loaded user object.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function getUser() {
    static $user = NULL;
    if (is_null($user)) {
      /* @var \Drupal\user\UserInterface $user */
      $user = $this->entityTypeManager->getStorage('user')->load($this->loadNode()->get('uid')->first()->getString());
    }
    return $user;
  }

  /**
   * {@inheritdoc}
   */
  protected function getResponseKeyCacheTags() {
    return ["node:{$this->loadNode()->id()}", "user:{$this->getUser()->id()}"];
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
  protected function getEntityTypeManager() {
    return $this->entityTypeManager;
  }

}
