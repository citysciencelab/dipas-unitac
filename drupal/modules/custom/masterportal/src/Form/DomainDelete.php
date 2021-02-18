<?php

namespace Drupal\masterportal\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\domain\Form\DomainDeleteForm;
use Drupal\masterportal\Service\DomainHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * {@inheritDoc}
 *
 * @package Drupal\masterportal\Form
 */
class DomainDelete extends DomainDeleteForm {

  /**
   * @var \Drupal\masterportal\Service\DomainHandlerInterface
   */
  protected $domainHandler;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('masterportal.domainhandler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(DomainHandlerInterface $domain_handler) {
    $this->domainHandler = $domain_handler;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->domainHandler->onDomainDelete($this->entity);
  }

}
