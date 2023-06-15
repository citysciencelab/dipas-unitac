<?php

namespace Drupal\dipas_stories;

/**
 * Helper functions to assist when dealing with loading entities.
 */
trait LoadEntityTrait {

  /**
   * Helper function to load an entity (singleton).
   *
   * @param String $entityTypeID
   *   The entity type id
   * @param int|String $entityID
   *   The entity id
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   */
  protected function getEntity($entityTypeID, $entityID) {
    /* @var \Drupal\Core\Entity\ContentEntityInterface $entities[] */
    $entities = drupal_static('dipas_stories.entities', []);

    if (!isset($entities[$entityTypeID])) {
      $entities[$entityTypeID] = [];
    }

    /* @var \Drupal\Core\Entity\EntityStorageInterface[] */
    $storageInterfaces = drupal_static('dipas_stories.storage_interfaces', []);

    if (!isset($storageInterfaces[$entityTypeID])) {
      $storageInterfaces[$entityTypeID] = $this->getEntityTypeManager()->getStorage($entityTypeID);
    }

    if (!isset($entities[$entityTypeID][$entityID])) {
      $entities[$entityTypeID][$entityID] = $storageInterfaces[$entityTypeID]->load($entityID);
    }

    return $entities[$entityTypeID][$entityID];
  }

  /**
   * Helper function to load multiple entities (singleton).
   *
   * @param String $entityTypeID
   *   The entity type id
   * @param int[]|String[] $entityIDs
   *   The entity ids
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface[]
   */
  protected function getEntities($entityTypeID, array $entityIDs) {
    $entities = [];

    foreach ($entityIDs as $id) {
      $entities[$id] = $this->getEntity($entityTypeID, $id);
    }

    return $entities;
  }

  /**
   * @return \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  abstract protected function getEntityTypeManager();

}
