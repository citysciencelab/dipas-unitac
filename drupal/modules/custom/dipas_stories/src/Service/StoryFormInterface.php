<?php

namespace Drupal\dipas_stories\Service;

use Drupal\Core\Form\FormStateInterface;
use \Drupal\node\NodeInterface;

interface StoryFormInterface {

  public function redirectHandler(array $form, FormStateInterface $form_state);

  public function addStoryStepHandler(array $form, FormStateInterface $form_state);

  public function deleteStoryStepSubmit(array $form, FormStateInterface $form_state);

  public function editOrDeleteStoryStepHandler(array $form, FormStateInterface $form_state);

  public function deleteAllAssociatedStorySteps(NodeInterface $story_id);

}
