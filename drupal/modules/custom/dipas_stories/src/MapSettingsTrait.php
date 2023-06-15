<?php

namespace Drupal\dipas_stories;

trait MapSettingsTrait {

  /**
   * Helper function to load a story node identified by it's ID (singleton).
   *
   * @param $nodeID
   *
   * @return array|\Drupal\node\NodeInterface|null
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getStoryNode($nodeID) {
    return $this->getEntity('node', $nodeID);
  }

  /**
   * Helper function to retrieve previously stored map settings from a story node.
   *
   * @param string $entity_type
   * @param string|int $entity_id
   *
   * @return NULL|stdClass
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function getMapSettingsFromStory($entity_type, $entity_id) {
    $story_map_settings = drupal_static('dipas_stories_story_map_settings', NULL);

    if (empty($story_map_settings)) {
      if ($entity_type === 'story_step') {
        $entity_id = $this->getAssociatedStoryNodeIDForStoryStep($entity_id);
      }

      if ($entity_id) {
        $node = $this->getStoryNode($entity_id);

        $story_map_settings = ($field = $node->get('field_map_settings')->first())
          ? json_decode($field->getString())
          : NULL;

        if ($story_map_settings) {
          if ($story_map_settings->ForegroundLayer->layerProperties) {
            $story_map_settings->ForegroundLayer->layerProperties = (array) $story_map_settings->ForegroundLayer->layerProperties;
          }

          if ($story_map_settings->BackgroundLayer->layerProperties) {
            $story_map_settings->BackgroundLayer->layerProperties = (array) $story_map_settings->BackgroundLayer->layerProperties;
          }
        }
      }
    }

    return $story_map_settings;
  }

  /**
   * Helper function to retrieve the map settings from the first story step.
   *
   * @param $storyID
   *
   * @return array|mixed|null
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function getMapSettingsFromFirstStoryStep($storyID) {
    $story = $this->getStoryNode($storyID);
    $step = ($field = $story->get('field_story_steps')->first())
      ? $field->get('target_id')->getString()
      : FALSE;

    return $step ? $this->getMapSettingsFromStoryStep($step) : NULL;
  }

  /**
   * Helper function: get MapSettings from a given Story Step identified by its ID.
   *
   * @param $stepID
   *
   * @return array|mixed|null
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getMapSettingsFromStoryStep($stepID) {
    $mapSettings = drupal_static('dipas_stories_story_step_map_settings', NULL);

    if (empty($mapSettings)) {
      $stepEntity = $this->getEntity('story_step', $stepID);

      $mapSettings = ($field = $stepEntity->get('field_map_settings')->first())
        ? json_decode($field->getString())
        : NULL;
    }

    return $mapSettings;
  }

  /**
   * @returns \Drupal\Core\Entity\ContentEntityInterface
   */
  abstract protected function getEntity($entityTypeID, $entityID);

  /**
   * @returns int|string
   */
  abstract public function getAssociatedStoryNodeIDForStoryStep($story_step_id);

}
