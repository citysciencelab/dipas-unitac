<?php

namespace Drupal\masterportal\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\domain\DomainForm;
use Drupal\domain\DomainStorageInterface;
use Drupal\domain\DomainValidatorInterface;
use Drupal\masterportal\Service\DomainHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * {@inheritDoc}
 *
 * @package Drupal\masterportal\Form
 */
class DomainEdit extends DomainForm {

  /**
   * @var \Drupal\masterportal\Service\DomainHandlerInterface
   */
  protected $domainHandler;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')->getStorage('domain'),
      $container->get('renderer'),
      $container->get('domain.validator'),
      $container->get('entity_type.manager'),
      $container->get('masterportal.domainhandler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    DomainStorageInterface $domain_storage,
    RendererInterface $renderer,
    DomainValidatorInterface $validator,
    EntityTypeManagerInterface $entity_type_manager,
    DomainHandlerInterface $domain_handler
  ) {
    parent::__construct($domain_storage, $renderer, $validator, $entity_type_manager);
    $this->domainHandler = $domain_handler;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);
    $this->domainHandler->onDomainEdit($this->entity);
  }

}
