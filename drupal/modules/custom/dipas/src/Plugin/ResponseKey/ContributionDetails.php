<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\ResponseKey;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ContributionDetails.
 *
 * @ResponseKey(
 *   id = "contributiondetails",
 *   description = @Translation("Returns the content for a given contribution id."),
 *   requestMethods = {
 *     "GET",
 *   },
 *   isCacheable = true
 * )
 *
 * @package Drupal\dipas\Plugin\ResponseKey
 */
class ContributionDetails extends ContributionNodeRequestBase {

  use RetrieveRatingTrait;
  use DateTimeTrait;

  /**
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * {@inheritdoc}
   */
  protected function setAdditionalDependencies(ContainerInterface $container) {
    $this->dateFormatter = $container->get('date.formatter');
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
    return $this->getNode()->id();
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   * @throws \Drupal\dipas\Exception\MalformedRequestException
   */
  public function getPluginResponse() {
    $this->checkRequest();
    $node = $this->getNode();
    return [
      'nid' => $node->id(),
      'title' => $node->label(),
      'text' => $node->get('field_text')->first()->getString(),
      'category' => (int) $node->get('field_category')->first()->getString(),
      'rubric' =>  ($rubric = $node->get('field_rubric')->first()) ? (int) $rubric->getString() : FALSE,
      'geodata' => ($geodata = $node->get('field_geodata')->first()) ? json_decode($geodata->getString()) : (object) [],
      'user' => $this->getUser($node->get('uid')->first()->getString())->label(),
      'rating' => $this->getRating(),
      'keywords' => ($field = $node->get('field_tags')) ? $field->getString() : '',
      'created' => $this->convertTimestampToUTCDateTimeString($node->get('created')->getString(), FALSE),
    ];
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
      $user = $this->entityTypeManager->getStorage('user')->load($this->getNode()->get('uid')->first()->getString());
    }
    return $user;
  }

  /**
   * {@inheritdoc}
   */
  protected function getResponseKeyCacheTags() {
    return ["node:{$this->getNode()->id()}", "user:{$this->getUser()->id()}"];
  }

  /**
   * {@inheritdoc}
   */
  protected function getDateFormatter() {
    return $this->dateFormatter;
  }

}
