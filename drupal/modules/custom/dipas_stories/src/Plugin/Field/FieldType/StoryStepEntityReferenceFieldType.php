<?php

namespace Drupal\dipas_stories\Plugin\Field\FieldType;

use Drupal\Core\Field\Annotation\FieldType;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Field\FieldItemBase;

/**
 * @FieldType(
 *   id = "story_step_entity_reference_field",
 *   label = @Translation("Story Step Entity Reference"),
 *   description = @Translation("An entity reference field for story steps"),
 *   category = @Translation("Reference"),
 *   default_widget = "story_step_entity_reference_widget",
 *   default_formatter = "story_step_field_formatter"
 * )
 */
class StoryStepEntityReferenceFieldType extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    // Datastructure for the tabledrag feature of Drupal
    $step_target_id_definition = DataDefinition::create('integer')->setLabel(new TranslatableMarkup('Target ID'));
    $step_weight_definition = DataDefinition::create('string')->setLabel(new TranslatableMarkup('Weight'));
    $step_pid_definition = DataDefinition::create('string')->setLabel(new TranslatableMarkup('Parent ID'));

    $properties['target_id'] = $step_target_id_definition;
    $properties['weight'] = $step_weight_definition;
    $properties['pid'] = $step_pid_definition;

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema['columns']['target_id'] = [
      'type' => 'int',
      'size' => 'big',
      'unsigned' => FALSE, // because we need a negativ flag like -1
    ];

    $schema['columns']['weight'] = [
      'type' => 'int',
      'size' => 'medium',
      'unsigned' => FALSE, // because we also want negative numbers for weights
    ];

    $schema['columns']['pid'] = [
      'type' => 'int',
      'size' => 'big',
      'unsigned' => TRUE,
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $isEmpty =
          empty($this->get('target_id')->getValue()) &&
          empty($this->get('weight')->getValue()) &&
          empty($this->get('pid')->getValue());

    return $isEmpty;
  }

}
