<?php

namespace Drupal\dipas_stories\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\Core\Http\RequestStack;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\dipas_stories\Entity\StoryStep;
use Drupal\dipas_stories\LoadEntityTrait;
use Drupal\dipas_stories\StoryRelationHandlerTrait;
use Drupal\dipas_stories\StoryStepTrait;

class StoryForm implements StoryFormInterface {

  use LoadEntityTrait,
    StoryStepTrait,
    StoryRelationHandlerTrait,
    StringTranslationTrait,
    DependencySerializationTrait;

  /**
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * @var \Drupal\dipas_stories\Service\DipasStoriesApiInterface
   */
  protected $dipasStoriesApi;

  public function __construct(
    RequestStack $request_stack,
    EntityTypeManagerInterface $entity_type_manager,
    Connection $database,
    RendererInterface $renderer,
    DipasStoriesApiInterface $dipas_stories_api
  ) {
    $this->currentRequest = $request_stack->getCurrentRequest();
    $this->entityTypeManager = $entity_type_manager;
    $this->database = $database;
    $this->renderer = $renderer;
    $this->dipasStoriesApi = $dipas_stories_api;
  }

  /**
   * {@inheritdoc}
   */
  public function redirectHandler(array $form, FormStateInterface $form_state) {
    if ($this->currentRequest->query->has('story_id')) {
      $form_state->setRedirect(
        'entity.node.edit_form',
        [
          'node' => $this->currentRequest->query->get('story_id'),
        ]
      );
    }
  }

  /**
   * Saves the story node when the form is submitted.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return void
   */
  protected function saveStoryNode(array $form, FormStateInterface $form_state) {
    $formObj = $form_state->getFormObject();
    $formObj->submitForm($form, $form_state);
    $formObj->save($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function addStoryStepHandler(array $form, FormStateInterface $form_state) {
    $this->saveStoryNode($form, $form_state);

    // Generate story step & save it
    $story_step = StoryStep::create();
    $story_step->save();

    $story_step_id = $story_step->id();

    /**
     * @var \Drupal\Core\Entity\ContentEntityInterface $entity
     */
    $entity = $form_state->getFormObject()->getEntity();

    $field_story_steps = $entity->get('field_story_steps')->getValue();

    // add new story step
    $field_story_steps[] = [
      'target_id' => $story_step_id,
      'weight' => 0,
      'pid' => 0,
    ];

    $entity->set('field_story_steps', $field_story_steps);

    $entity->save();

    $story_id = $entity->id();

    // $form_state->setRedirect() did not work reliably
    $response = new TrustedRedirectResponse(
      Url::fromRoute(
        'entity.story_step.edit_form',
        [
          'story_step' => $story_step_id,
          'story_id' => $story_id,
          'process_status' => 'create',
        ]
      )->toString()
    );

    $response->send();
  }

  /**
   *  {@inheritdoc}
   */
  public function nextStoryStepHandler(array $form, FormStateInterface $form_state) {
    $this->saveStoryNode($form, $form_state);

    //create a new story step programmtically
    $story_id = $this->currentRequest->query->get('story_id');
    $story_step = StoryStep::create();
    $story_step->save();
    $story_step_id = $story_step->id();
    // get the story entity & save the story step reference
    $story = $this->getEntity('node', $story_id);
    $field_story_steps = $story->get('field_story_steps')->getValue();
    // add new story step
    $field_story_steps[] = [
      'target_id' => $story_step_id,
      'weight' => 0,
      'pid' => 0,
    ];
    $story->set('field_story_steps', $field_story_steps);
    $story->save();
    // go to the edit form of the new created story step
    $form_state->setRedirect('entity.story_step.edit_form', ['story_step' => $story_step_id, 'story_id' => $story_id]);
  }

  /**
   *  {@inheritdoc}
   */
  public function editOrDeleteStoryStepHandler(array $form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();

    if ($trigger['#action'] === 'edit') {
      $this->saveStoryNode($form, $form_state);
    }

    // Had to actually send a response myself bc $form_state->setRedirect() etc did not work.
    $response = new TrustedRedirectResponse(
      Url::fromRoute(
        sprintf('entity.story_step.%s_form', $trigger['#action']),
        [
          'story_step' => $trigger['#story_step_id'],
          'story_id' => $form_state->getFormObject()->getEntity()->id(),
          'process_status' => 'edit',
        ]
      )->toString()
    );

    $response->send();
  }

  /**
   * {@inheritdoc}
   */
  public function deleteStoryStepSubmit(array $form, FormStateInterface $form_state) {
    // Determine the story step to be deleted
    $deletedStepEntity = $form_state->getFormObject()->getEntity();

    // Determine the associated Story node for that step
    $associatedStoryNodeID = $this->getAssociatedStoryNodeIDForStoryStep($deletedStepEntity->id());

    // Load the story
    $story = $this->getEntity('node', $associatedStoryNodeID);

    // The current referenced steps field value
    $referenceValue = $story->get('field_story_steps')->getValue();

    // Determine the current setting of the deleted story step
    $hierarchySetting = array_filter(
      $referenceValue,
      function ($elem) use ($deletedStepEntity) {
        return (int) $elem['target_id'] === (int) $deletedStepEntity->id();
      }
    );
    $hierarchySetting = array_pop($hierarchySetting);

    // Filter out the field entry pointing to the deleted story step
    $associatedSteps = array_filter(
      $referenceValue,
      function ($elem) use ($deletedStepEntity) {
        return (int) $elem['target_id'] !== (int) $deletedStepEntity->id();
      }
    );

    // Re-set potential childs of the deleted step to the configured parent of the deleted step
    array_walk(
      $associatedSteps,
      function (&$entry) use ($deletedStepEntity, $hierarchySetting) {
        if ((int) $entry['pid'] === (int) $deletedStepEntity->id()) {
          $entry['pid'] = $hierarchySetting['pid'];
        }
      }
    );

    // Re-Set the reference field value and save the story.
    $story->set('field_story_steps', $associatedSteps);
    $story->save();

    // Redirect
    $form_state->setRedirect(
      'entity.node.edit_form',
      [
        'node' => $associatedStoryNodeID
      ]
    );
  }

  /**
   * Deletes all story steps that are associated with a story
   *
   * @var \Drupal\node\NodeInterface $node
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function deleteAllAssociatedStorySteps(NodeInterface $story) {
    foreach($story->field_story_steps as $reference) {
      $story_step_ids[] = (int) $reference->target_id;
    }
    $story_steps = $this->getEntities('story_step', $story_step_ids);
    array_map(
      function($story_step) {
        return $story_step->delete();
      },
      $story_steps
    );
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
