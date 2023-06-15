<?php

namespace Drupal\dipas_stories;

trait StoryRelationHandlerTrait {

  /**
   * Determines the node id of the story node pointing to a specific story step entity.
   *
   * @param string|int $story_step_id
   *   The ID of the story step entity.
   *
   * @return mixed|null
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getAssociatedStoryNodeIDForStoryStep($story_step_id) {
    $associated_story_id = drupal_static('dipas_stories_story_id', NULL);

    if (empty($associated_story_id)) {
      $entityQuery = $this->getEntityTypeManager()->getStorage('node')->getQuery();

      $entity_id = $entityQuery
        ->condition('type', 'story', '=')
        ->condition('field_story_steps.target_id', $story_step_id, 'IN')
        ->execute();

      $associated_story_id = array_shift($entity_id);
    }

    return $associated_story_id;
  }

  /**
   * @return \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  abstract protected function getEntityTypeManager();

}
