<?php

namespace Drupal\dipas_dev\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\dipas_dev\Service\DipasDevService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DipasDev extends FormBase {

  protected $dipasDevService;

  /**
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dipas_dev.service'),
      $container->get('messenger')
    );
  }

  public function __construct(
    DipasDevService $dipasDevService,
    MessengerInterface $messenger
  ) {
    $this->dipasDevService = $dipasDevService;
    $this->messenger = $messenger;
  }

  public function getFormId() {
    return 'DipasDev';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['fix_domain_entries'] = [
      '#type' => 'submit',
      '#value' => 'Fix Domain entries',
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->dipasDevService->modifyDomainRecordsForDev();
  }

}
