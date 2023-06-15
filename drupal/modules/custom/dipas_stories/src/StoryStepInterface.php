<?php

namespace Drupal\dipas_stories;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a story step entity type.
 */
interface StoryStepInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

  /**
   * Gets the story step title.
   *
   * @return string
   *   Title of the story step.
   */
  public function getTitle();

  /**
   * Sets the story step title.
   *
   * @param string $title
   *   The story step title.
   *
   * @return \Drupal\dipas_stories\StoryStepInterface
   *   The called story step entity.
   */
  public function setTitle($title);

  /**
   * Gets the story step creation timestamp.
   *
   * @return int
   *   Creation timestamp of the story step.
   */
  public function getCreatedTime();

  /**
   * Sets the story step creation timestamp.
   *
   * @param int $timestamp
   *   The story step creation timestamp.
   *
   * @return \Drupal\dipas_stories\StoryStepInterface
   *   The called story step entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the story step status.
   *
   * @return bool
   *   TRUE if the story step is enabled, FALSE otherwise.
   */
  public function isEnabled();

  /**
   * Sets the story step status.
   *
   * @param bool $status
   *   TRUE to enable this story step, FALSE to disable.
   *
   * @return \Drupal\dipas_stories\StoryStepInterface
   *   The called story step entity.
   */
  public function setStatus($status);

}
