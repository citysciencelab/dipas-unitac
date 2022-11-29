<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\ResponseKey;

/**
 * Class Schedule.
 *
 * @ResponseKey(
 *   id = "schedule",
 *   description = @Translation("Returns the content for the schedule listing."),
 *   requestMethods = {
 *     "GET",
 *   },
 *   isCacheable = true
 * )
 *
 * @package Drupal\dipas\Plugin\ResponseKey
 */
class Schedule extends PagedNodeListingBase {

  /**
   * {@inheritdoc}
   */
  protected function getNodeType() {
    return 'appointment';
  }

  /**
   * {@inheritdoc}
   */
  protected function getConditions() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  function getExpressions() {
    return [
      'CASE WHEN COALESCE(LENGTH(date.field_date_end_value), 0) > 0 THEN date.field_date_end_value ELSE date.field_date_value END' => 'expires'
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getJoins() {
    return [
      [
        'type' => 'LEFT',
        'table' => 'node__field_topic',
        'alias' => 'topic',
        'condition' => 'base.type = topic.bundle AND base.nid = topic.entity_id AND base.vid = topic.revision_id AND attr.langcode = topic.langcode AND topic.deleted = 0',
        'fields' => [
          'field_topic_value' => 'topic',
        ],
      ],
      [
        'type' => 'LEFT',
        'table' => 'node__field_description',
        'alias' => 'description',
        'condition' => 'base.type = description.bundle AND base.nid = description.entity_id AND base.vid = description.revision_id AND attr.langcode = description.langcode AND description.deleted = 0',
        'fields' => [
          'field_description_value' => 'description',
        ],
      ],
      [
        'type' => 'LEFT',
        'table' => 'node__field_date',
        'alias' => 'date',
        'condition' => 'base.type = date.bundle AND base.nid = date.entity_id AND base.vid = date.revision_id AND attr.langcode = date.langcode AND date.deleted = 0',
        'fields' => [
          'field_date_value' => 'start',
          'field_date_end_value' => 'end',
        ],
      ],
      [
        'type' => 'LEFT',
        'table' => 'node__field_address',
        'alias' => 'address',
        'condition' => 'base.type = address.bundle AND base.nid = address.entity_id AND base.vid = address.revision_id AND attr.langcode = address.langcode AND address.deleted = 0',
        'fields' => [
          'field_address_organization' => 'organizer',
          'field_address_address_line1' => 'street1',
          'field_address_address_line2' => 'street2',
          'field_address_postal_code' => 'zip',
          'field_address_locality' => 'city',
        ],
      ],
      [
        'type' => 'LEFT',
        'table' => 'node__field_geodata',
        'alias' => 'localization',
        'condition' => 'base.type = localization.bundle AND base.nid = localization.entity_id AND base.vid = localization.revision_id AND attr.langcode = localization.langcode AND localization.deleted = 0',
        'fields' => [
          'field_geodata_value' => 'geom',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getSortingField() {
    return 'field_date_value';
  }

  /**
   * {@inheritdoc}
   */
  protected function getSortingDirection() {
    return 'ASC';
  }

  /**
   * {@inheritdoc}
   */
  protected function postProcessNodeData(array &$nodes) {
    array_walk($nodes, function (&$node) {
      $node->start = $this->convertTimestampToUTCDateTimeString(strtotime($node->start), TRUE);
      if ($node->end) {
        $node->end = $this->convertTimestampToUTCDateTimeString(strtotime($node->end), TRUE);
      }

      if ($node->geom) {
        $node->geom = json_decode($node->geom);
        $node->lon = $node->geom->centerPoint->coordinates[0];
        $node->lat = $node->geom->centerPoint->coordinates[1];
      }
      else {
        $node->lon = null;
        $node->lat = null;
      }
    });
  }

}
