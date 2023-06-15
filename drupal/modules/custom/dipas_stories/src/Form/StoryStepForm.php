<?php

namespace Drupal\dipas_stories\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the story step entity edit forms.
 */
class StoryStepForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {

    $entity = $this->getEntity();
    $result = $entity->save();
    $link = $entity->toLink($this->t('View'))->toRenderable();

    $message_arguments = ['%label' => $this->entity->label()];
    $logger_arguments = $message_arguments + ['link' => render($link)];

    if ($result == SAVED_NEW) {
      $this->messenger()->addStatus($this->t('New story step %label has been created.', $message_arguments));
      $this->logger('dipas_stories')->notice('Created new story step %label', $logger_arguments);
    }
    else {
      $this->messenger()->addStatus($this->t('The story step %label has been updated.', $message_arguments));
      $this->logger('dipas_stories')->notice('Updated new story step %label.', $logger_arguments);
    }

    $form_state->setRedirect('entity.story_step.canonical', ['story_step' => $entity->id()]);
  }

}
