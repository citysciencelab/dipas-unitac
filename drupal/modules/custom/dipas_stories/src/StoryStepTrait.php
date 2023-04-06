<?php

namespace Drupal\dipas_stories;

/**
 * Helper function when dealing with Story Step tree data.
 */
trait StoryStepTrait {

  /**
   * Retrieves the field value of Story Step reference fields soryted by delta
   * and with an added depth property.
   *
   * @param string $entity_type_id
   * @param int|string $entity_id
   * @param string $fieldname
   *
   * @return array
   */
  protected function getStoryStepReferenceFieldData($entity_type_id, $entity_id, $fieldname) {
    $data = $this->getDatabase()
      ->select(sprintf('%s__%s', $entity_type_id, $fieldname), 't')
      ->fields('t')
      ->condition('entity_id', $entity_id)
      ->orderBy('t.delta')
      ->execute()
      ->fetchAll();

    $data = array_combine(
      array_map(
        function ($row) use ($fieldname) {
          return (int) $row->{sprintf('%s_target_id', $fieldname)};
        },
        $data
      ),
      $data
    );

    foreach ($data as &$row) {
      $row->depth = (int) $row->{sprintf('%s_pid', $fieldname)} === 0
        ? 0
        : $data[$row->{sprintf('%s_pid', $fieldname)}]->depth + 1;
    }

    return $data;
  }

  /**
   * @return \Drupal\Core\Database\Connection
   */
  abstract protected function getDatabase();

}
