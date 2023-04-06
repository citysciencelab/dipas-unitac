<?php

namespace Drupal\dipas_stories\Plugin\Field\FieldFormatter;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\Annotation\FieldFormatter;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\dipas_stories\LoadEntityTrait;
use Drupal\dipas_stories\StoryStepTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @FieldFormatter(
 *   id = "story_step_field_formatter",
 *   label = @Translation("Story Step Field Formatter"),
 *   description = @Translation("Table of contents formatter"),
 *   field_types = {
 *      "story_step_entity_reference_field",
 *   }
 * )
 */
class StoryStepEntityReferenceFieldFormatter extends FormatterBase {

  use StoryStepTrait, LoadEntityTrait;

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('database'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    $label,
    $view_mode,
    array $third_party_settings,
    Connection $database,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    parent::__construct(
      $plugin_id,
      $plugin_definition,
      $field_definition,
      $settings,
      $label,
      $view_mode,
      $third_party_settings
    );
    $this->database = $database;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $nid = \Drupal::routeMatch()->getRawParameter('node');
    $element = [];
    $table['table-row'] = [
      '#type' => 'table',
      '#empty' => $this->t('Sorry, There are no items!'),
    ];

    $result = $this->getStoryStepReferenceFieldData('node', $nid, 'field_story_steps');

    foreach($result as $row) {
      $table['table-row'][$row->field_story_steps_target_id]['#attributes']['class'][] = 'draggable';
      // Indent item on load
      $indentation = [
          '#theme' => 'indentation',
          '#size' => $row->depth,
      ];

      $table['table-row'][$row->field_story_steps_target_id]['name'] = [
        '#markup' => $this->getEntity('story_step', $row->field_story_steps_target_id)->label(),
        '#prefix' => !empty($indentation) ? \Drupal::service('renderer')->render($indentation) : '',
      ];
    }
    $element[0] = $table;

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityTypeManager() {
    return $this->entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  protected function getDatabase() {
    return $this->database;
  }


}
