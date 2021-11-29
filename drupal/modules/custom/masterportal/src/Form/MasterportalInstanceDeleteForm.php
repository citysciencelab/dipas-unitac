<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\ConfirmFormHelper;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\masterportal\Entity\MasterportalInstance;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class MasterportalInstanceDeleteForm.
 *
 * Provides a form that is displayed when the delete operation
 * is requested for masterportal_instance entities.
 *
 * @package Drupal\masterportal\Form
 */
class MasterportalInstanceDeleteForm extends EntityConfirmFormBase {

  /**
   * The custom logger channel for this module.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('logger.channel.masterportal'),
      $container->get('current_user')
    );
  }

  /**
   * MasterportalInstanceDeleteForm constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   Our custom logger channel.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The user accessing this form.
   */
  public function __construct(
    LoggerChannelInterface $logger,
    AccountInterface $current_user
  ) {
    $this->logger = $logger;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    // Deny the deletion of protected entities.
    if (in_array($this->entity->id(), MasterportalInstance::persistentInstances())) {
      throw new HttpException(403, 'Unauthorized');
    }
    return $this->t(
      'Are you sure you want to delete the instance configuration "%name"?',
      [
        '%name' => $this->entity->label(),
      ],
      ['context' => 'Masterportal']
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('masterportal.settings.instances');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete', [], ['context' => 'Masterportal']);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    return [
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->getConfirmText(),
        '#submit' => [
          [$this, 'submitForm'],
        ],
      ],
      'cancel' => ConfirmFormHelper::buildCancelLink($this, $this->getRequest()),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->delete();
    $this->logger->notice(
      'Masterportal instance "%name" has been deleted by %user.',
      [
        '%name' => $this->entity->label(),
        '%user' => $this->currentUser->getDisplayName(),
      ]
    );
    \Drupal::messenger()->addMessage(
      $this->t(
        'Masterportal instance "%name" has been deleted.',
        ['%name' => $this->entity->label()],
        ['context' => 'Masterportal']
      )
    );
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
