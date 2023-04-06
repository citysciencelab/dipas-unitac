<?php

namespace Drupal\dipas_stories\Plugin\Field\FieldWidget;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\Annotation\FieldWidget;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\dipas_stories\LoadEntityTrait;
use Drupal\dipas_stories\Service\StoryFormInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @FieldWidget(
 *   id = "story_step_entity_reference_widget",
 *   label = @Translation("Story Steps Ordering"),
 *   description = @Translation("Field Widget to order story steps"),
 *   multiple_values = true,
 *   field_types = {
 *    "story_step_entity_reference_field"
 *   }
 * )
 */
class StoryStepEntityReferenceWidget extends WidgetBase {

  use LoadEntityTrait;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * @var \Drupal\dipas_stories\Plugin\Field\FieldWidget\StoryFormInterface
   */
  protected $storyFormHandler;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('entity_type.manager'),
      $container->get('renderer'),
      $container->get('dipas_stories.story_form')
    );
  }

  /**
   * StoryStepEntityReferenceWidget constructor
   *
   * @param string $plugin_id
   *   The plugin_id for the widget.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the widget is associated.
   * @param array $settings
   *   The widget settings.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param EntityTypeManagerInterface $entity_type_manager
   *   Drupal's entity type manager service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   Drupal's html render service.
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    array $third_party_settings,
    EntityTypeManagerInterface $entity_type_manager,
    RendererInterface $renderer,
    StoryFormInterface $story_form
  ) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->entityTypeManager = $entity_type_manager;
    $this->renderer = $renderer;
    $this->storyFormHandler = $story_form;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'maxNestingDepth' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return [
      'maxNestingDepth' => [
        '#type' => 'select',
        '#title' => $this->t('Maximum nesting depth', [], ['context' => 'dipas_stories']),
        '#options' => array_merge(
          [
              'FALSE' => $this->t('Unlimited', [], ['context' => 'dipas_stories']),
              0 => $this->t('No nesting allowed', [], ['context' => 'dipas_stories']),
            ],
            range(1, 10),
        ),
        '#default_value' => $this->getSetting('maxNestingDepth'),
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    switch ($this->getSetting('maxNestingDepth')) {
      case 'FALSE':
        $limit = $this->t('Unlimited', [], ['context' => 'dipas_stories']);
        break;

      case '0':
        $limit = $this->t('No nesting allowed', [], ['context' => 'dipas_stories']);
        break;

      default:
        $limit = $this->t('@limit level(s)', ['@limit' => $this->getSetting('maxNestingDepth')], ['context' => 'dipas_stories']);
    }

    return [
      $this->t(
        'Maximum nesting depth: @maxNestingDepth',
        ['@maxNestingDepth' => $limit],
        ['context' => 'dipas_stories']
      ),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $nestingLimit = $this->getSetting('maxNestingDepth') === 'FALSE'
      ? FALSE
      : (int) $this->getSetting('maxNestingDepth');

    $element = [
      'entitytable' => [
        '#type' => 'table',
        '#header' => [
          $this->t('Story Step Title', [], ['context' => 'dipas_stories']),
          $this->t('Weight'), // weight column
          $this->t('Actions'),
        ],
        '#empty' => $this->t(
          'No story steps defined at this time. Use the button below to add a new story step.',
          [],
          ['context' => 'dipas_stories']
        ),
        '#tabledrag' => [
          [
            'action' => 'order',
            'relationship' => 'sibling',
            'group' => 'weight',
          ]
        ],
      ],
    ];

    if ($nestingLimit !== 0) {
      array_splice(
        $element['entitytable']['#header'],
        3,
        0,
        $this->t('Parent ID', [], ['context' => 'dipas_stories'])->__toString()
      );

      $element['entitytable']['#tabledrag'][] = [
        'action' => 'match',
        'relationship' => 'parent',
        'group' => 'parentid',
        'source' => 'stepid',
        'hidden' => TRUE,
        'limit' => $nestingLimit,
      ];
    }

    // Retrieve the referenced entity IDs ordered by their respective delta
    $referencedEntityIDs = array_map(
      function ($row) {
        return $row['target_id'];
      },
      $items->getValue()
    );

    // Prepare the field value.
    $fieldValues = array_combine(
      $referencedEntityIDs,
      array_map(
        function ($stepid, $index) use ($items) {
          return [
            'id' => $stepid,
            'label' => $this->getEntity('story_step', $stepid)->label(),
            'parentid' => $items->getValue()[$index]['pid'],
            'weight' => $items->getValue()[$index]['weight'],
          ];
        },
        $referencedEntityIDs,
        array_keys($referencedEntityIDs)
      )
    );

    // Add a depth property to each row.
    foreach ($fieldValues as &$value) {
      $value['depth'] = (int) $value['parentid'] === 0
        ? 0
        : $fieldValues[$value['parentid']]['depth'] + 1;
    }

    // Integrate a table row for each referenced step entity.
    foreach ($fieldValues as $row) {
      if ($row['depth'] > 0) {
        $indentation = [
          '#theme' => 'indentation',
          '#size' => $row['depth'],
        ];
      }

      $element['entitytable'][$row['id']] = [
        '#attributes' => [
          'class' => ['draggable'],
        ],
        'name' => [
          '#weight' => 0,
          '#type' => 'markup',
          '#markup' => $row['label'],
          '#prefix' => $row['depth'] > 0 ? $this->renderer->render($indentation) : '',
        ],
        'weight' => [
          '#weight' => 1,
          '#type' => 'weight',
          '#title' => $row['id'],
          '#title_display' => 'invisible',
          '#default_value' => $row['weight'],
          '#attributes' => [
            'class' => ['weight'],
          ],
        ],
        'actions' => [
          '#weight' => 3,
          'edit' => [
            '#type' => 'submit',
            '#value' => $this->t('Edit'),
            '#name' => 'story-step-button-edit-' . $row['id'],
            '#action' => 'edit',
            '#story_step_id' => $row['id'],
            '#submit' => [[$this->storyFormHandler, 'editOrDeleteStoryStepHandler']],
          ],

          'delete' => [
            '#type' => 'submit',
            '#value' => $this->t('Delete'),
            '#name' => 'story-step-button-delete' . $row['id'],
            '#action' => 'delete',
            '#story_step_id' => $row['id'],
            '#submit' => [[$this->storyFormHandler, 'editOrDeleteStoryStepHandler']],
          ],
        ],
      ];

      if ($nestingLimit !== 0) {
        $element['entitytable'][$row['id']]['parent'] = [
          '#weight' => 2,
          'id' => [
            '#type' => 'hidden',
            '#value' => $row['id'],
            '#attributes' => [
              'class' => ['stepid'],
            ]
          ],
          'pid' => [
            '#type' => $nestingLimit !== 0 ? 'number' : 'hidden',
            '#size' => 3,
            '#min' => 0,
            '#title' => $this->t('Parent ID', [], ['context' => 'dipas_stories']),
            '#default_value' => $row['parentid'],
            '#attributes' => [
              'class' => ['parentid'],
            ],
          ],
        ];
      }
    }

    $element['add_more'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Add Story Step', [], ['context' => 'dipas_stories']),
      '#submit' => [[$this->storyFormHandler, 'addStoryStepHandler']],
      '#attributes' => [
        'style' => 'margin-bottom: 20px;',
      ],
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $processedValues = [];

    if (isset($values['entitytable']) && is_array($values['entitytable'])) {
      foreach ($values['entitytable'] as $stepid => $row) {
        $processedValues[] = [
          'target_id' => $stepid,
          'weight' => $row['weight'],
          'pid' => $row['parent']['pid'],
        ];
      }
    }

    return $processedValues;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityTypeManager() {
    return $this->entityTypeManager;
  }

}
