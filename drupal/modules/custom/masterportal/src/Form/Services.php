<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Class Services.
 *
 * Contains the settings form potion defining the services settings object
 * for Materportal integrations.
 *
 * @package Drupal\masterportal\Form
 */
class Services extends MasterportalSettingsBase {

  use ElementValidateJsonTrait;
  use MultivalueRowTrait;

  /**
   * {@inheritdoc}
   */
  public function getForm(array $form, FormStateInterface $form_state) {
    // The fields inside this form are nested (same field name
    // on different branches).
    $form['#tree'] = TRUE;

    // The "outer container" of the data rows.
    $form['services'] = ['#type' => 'container'];

    $this->createMultivalueFormPortion(
      $form['services'],
      'services',
      $form_state,
      $this->services ?: [],
      'No services defined. Click the "Add service" button to add new services.'
    );

    // Preview link for the current JSON feed at the bottom of the form.
    $form['preview'] = [
      '#weight' => 999999,
      '#type' => 'html_tag',
      '#tag' => 'p',
      [
        '#type' => 'markup',
        '#markup' => $this->t(
          'Click the @testlink to get a preview of this feed in a new browser tab.',
          [
            '@testlink' => Link::fromTextAndUrl(
              'Testlink',
              Url::fromRoute(
                'masterportal.services',
                [],
                [
                  'attributes' => ['target' => '_blank'],
                ]
              ))->toString(),
          ],
          ['context' => 'Masterportal']
        ),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function getInputRow($property, $delta, array $row_defaults, FormStateInterface $form_state) {
    return [
      'id' => [
        '#type' => 'textfield',
        '#title' => $this->t('ID', [], ['context' => 'Masterportal']),
        '#description' => $this->t('The service ID.', [], ['context' => 'Masterportal']),
        '#required' => TRUE,
        '#size' => 10,
        '#default_value' => isset($row_defaults['id']) ? $row_defaults['id'] : NULL,
        '#inline' => TRUE,
      ],
      'name' => [
        '#type' => 'textfield',
        '#title' => $this->t('Name', [], ['context' => 'Masterportal']),
        '#description' => $this->t('The name of this REST service.', [], ['context' => 'Masterportal']),
        '#required' => TRUE,
        '#size' => 30,
        '#default_value' => isset($row_defaults['name']) ? $row_defaults['name'] : NULL,
        '#inline' => TRUE,
      ],
      'url' => [
        '#type' => 'textfield',
        '#title' => $this->t('URL', [], ['context' => 'Masterportal']),
        '#description' => $this->t('The URL of this REST service.', [], ['context' => 'Masterportal']),
        '#required' => TRUE,
        '#size' => 50,
        '#default_value' => isset($row_defaults['url']) ? $row_defaults['url'] : NULL,
        '#inline' => TRUE,
      ],
      'type' => [
        '#type' => 'select',
        '#title' => $this->t('Type', [], ['context' => 'Masterportal']),
        '#description' => $this->t('The service type.', [], ['context' => 'Masterportal']),
        '#options' => [
          'CSW' => 'CSW',
          'URL' => 'URL',
          'ID' => 'ID',
          'GDZ' => 'GDZ',
          'WFS' => 'WFS',
          'WPS' => 'WPS',
          'EmailService' => 'EmailService',
          'PrintService' => 'PrintService',
          'Print' => 'Print',
        ],
        '#required' => TRUE,
        '#default_value' => isset($row_defaults['type']) ? $row_defaults['type'] : NULL,
        '#inline' => TRUE,
      ],
      'customOptions' => [
        '#type' => 'textfield',
        '#title' => $this->t('Custom properties', [], ['context' => 'Masterportal']),
        '#description' => $this->t('Enter custom properties of this service in a valid JSON object (if needed).', [], ['context' => 'Masterportal']),
        '#required' => FALSE,
        '#size' => 50,
        '#default_value' => isset($row_defaults['customOptions']) ? $row_defaults['customOptions'] : NULL,
        '#element_validate' => [[$this, 'validateJsonInput']],
        '#inline' => TRUE,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getAddRowButtonTitle($property) {
    return 'Add service';
  }

  /**
   * {@inheritdoc}
   */
  protected function getDataToAdd($property, array $current_state, array $user_input, $addSelectorValue, FormStateInterface $form_state) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  protected function allowMultipleEmptyAdds($property) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  protected function preSave($key, &$value, array $formPortion, FormStateInterface $form_state) {
    if ($key === 'services') {
      $value = self::getData('services', $form_state->getUserInput());
    }
  }

}
