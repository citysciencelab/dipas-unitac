<?php

namespace Drupal\dipas\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class NavigatorSettings extends FormBase {

  /**
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('state'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Navigator form constructor.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   Drupal's key/value storage service
   */
  public function __construct(
    StateInterface $state,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->state = $state;
    $this->nodeStorage = $entity_type_manager->getStorage('node');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dipas.navigator.settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $defaultPages = $this->nodeStorage->getQuery()
      ->condition('type', 'page', '=')
      ->condition('status', '1', '=')
      ->condition('field_domain_access', 'default', 'IN')
      ->execute();

    $defaultPages = array_map(
      function (NodeInterface $node) {
        return sprintf('%s (Node ID: %d)', $node->getTitle(), $node->id());
      },
      $this->nodeStorage->loadMultiple($defaultPages)
    );

    $form['#tree'] = TRUE;

    $form['settings'] = [
      '#theme_wrappers' => ['vertical_tabs'],
      'vertical_tabs' => [
        '#type' => 'vertical_tabs',
      ],

      'menu' => [
        '#type' => 'details',
        '#title' => $this->t('Footer menu'),
        '#description' => $this->t('Settings for the DIPAS navigator footer menu'),
        '#description_display' => 'before',

        'privacy' => [
          '#type' => 'select',
          '#title' => $this->t('Privacy page'),
          '#description' => $this->t('Please select the node for the privacy page.'),
          '#options' => $defaultPages,
          '#default_value' => $this->state->get('dipas.navigator.menu.privacy'),
          '#required' => TRUE,
        ],

        'imprint' => [
          '#type' => 'select',
          '#title' => $this->t('Imprint page'),
          '#description' => $this->t('Please select the node for the imprint page.'),
          '#options' => $defaultPages,
          '#default_value' => $this->state->get('dipas.navigator.menu.imprint'),
          '#required' => TRUE,
        ],

        'accessibility' => [
          '#type' => 'select',
          '#title' => $this->t('Accessibility page'),
          '#description' => $this->t('Please select the node for the accessibility page.'),
          '#options' => $defaultPages,
          '#default_value' => $this->state->get('dipas.navigator.menu.accessibility'),
          '#required' => TRUE,
        ],

        'about' => [
          '#type' => 'select',
          '#title' => $this->t('About navigator page'),
          '#description' => $this->t('Please select the node for the about navigator page.'),
          '#options' => $defaultPages,
          '#default_value' => $this->state->get('dipas.navigator.menu.about'),
          '#required' => TRUE,
        ],
      ],
    ];

    $form['actions'] = [
      '#type' => 'actions',

      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Save'),
        '#button_type' => 'primary',
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $this->state->set('dipas.navigator.menu.privacy', $values['settings']['menu']['privacy']);
    $this->state->set('dipas.navigator.menu.imprint', $values['settings']['menu']['imprint']);
    $this->state->set('dipas.navigator.menu.accessibility', $values['settings']['menu']['accessibility']);
    $this->state->set('dipas.navigator.menu.about', $values['settings']['menu']['about']);

    $this->messenger()->addMessage($this->t('Your settings have been saved!'));
  }

}
