<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\SettingsSection;

use CommerceGuys\Addressing\AddressFormat\AddressField;
use Drupal\Component\DependencyInjection\Container;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\masterportal\Form\MultivalueRowTrait;

/**
 * Class ProjectInformation.
 *
 * @SettingsSection(
 *   id = "ProjectInformation",
 *   title = @Translation("Project information"),
 *   description = @Translation("Basic project settings."),
 *   weight = 0,
 *   affectedConfig = {}
 * )
 *
 * @package Drupal\dipas\Plugin\SettingsSection
 */
class ProjectInformation extends SettingsSectionBase {

  use MultivalueRowTrait;
  use MediaSelectionTrait;

  /**
   * Drupal's entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Node storage object.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * Media storage object.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $mediaStorage;

  /**
   * {@inheritdoc}
   */
  protected function setAdditionalDependencies(Container $container) {
    $this->entityTypeManager = $container->get('entity_type.manager');
    $this->nodeStorage = $this->entityTypeManager->getStorage('node');
    $this->mediaStorage = $this->entityTypeManager->getStorage('media');
  }

  /**
   * {@inheritdoc}
   */
  public static function getDefaults() {
    return [
      'site_name' => '',
      'site_email_name' => '',
      'site_email_address' => '',
      'department' => '',
      'street1' => '',
      'street2' => '',
      'zip' => '',
      'city' => '',
      'contact_email' => '',
      'contact_telephone' => '',
      'contact_website' => '',
      'project_logo' => '',
      'partner_logos' => [],
      'welcome_modal' => [
        'headline' => '',
        'text' => '',
        'project_image' => '',
      ],
      'data_responsible' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array $form, FormStateInterface $form_state) {

    $section = [
      'site_settings' => [
        '#type' => 'fieldset',
        '#title' => $this->t('Site-wide settings', [], ['context' => 'DIPAS']),
        '#description' => $this->t('Settings that are directly related to the website configuration.', [], ['context' => 'DIPAS']),

        'site_name' => [
          '#type' => 'textfield',
          '#title' => $this->t('Project title', [], ['context' => 'DIPAS']),
          '#description' => $this->t('The name of this DIPAS process.', [], ['context' => 'DIPAS']),
          '#required' => TRUE,
          '#default_value' => $this->site_name,
        ],
        'site_email_name' => [
          '#type' => 'textfield',
          '#title' => $this->t('Email: Sender name', [], ['context' => 'DIPAS']),
          '#description' => $this->t('The name that is used when emails are sent. Leave blank to use the project title above.', [], ['context' => 'DIPAS']),
          '#default_value' => $this->site_email_name,
        ],
        'site_email_address' => [
          '#type' => 'textfield',
          '#title' => $this->t('Email: sender address ("from")', [], ['context' => 'DIPAS']),
          '#description' => $this->t('The address that is used as the sender email address in outgoing emails.', [], ['context' => 'DIPAS']),
          '#required' => TRUE,
          '#default_value' => $this->site_email_address,
        ],
      ],

      'project_contact' => [
        '#type' => 'fieldset',
        '#title' => $this->t('Project contact', [], ['context' => 'DIPAS']),
        '#description' => $this->t('Enter the contact details of the department responsible for this DIPAS project. This information will only be used visually/non-functional.', [], ['context' => 'DIPAS']),

        'contact_details' => [
          '#type' => 'address',
          '#title' => $this->t('Department contact information', [], ['context' => 'DIPAS']),
          '#required' => TRUE,
          '#default_value' => [
            'organization' => $this->department,
            'address_line1' => $this->street1,
            'address_line2' => $this->street2,
            'postal_code' => $this->zip,
            'locality' => $this->city,
            'country_code' => 'DE',
            'given_name' => NULL,
            'additional_name' => NULL,
            'family_name' => NULL,
            'sorting_code' => NULL,
            'dependent_locality' => NULL,
            'administrative_area' => NULL,
            'langcode' => NULL,
          ],
          '#available_countries' => ['DE'],
          '#used_fields' => [
            AddressField::ORGANIZATION,
            AddressField::ADDRESS_LINE1,
            AddressField::ADDRESS_LINE2,
            AddressField::POSTAL_CODE,
            AddressField::LOCALITY,
          ],
        ],

        'contact_telephone' => [
          '#type' => 'tel',
          '#title' => $this->t('Telephone number', [], ['context' => 'DIPAS']),
          '#required' => TRUE,
          '#default_value' => $this->contact_telephone,
        ],

        'contact_email' => [
          '#type' => 'email',
          '#title' => $this->t('Email address', [], ['context' => 'DIPAS']),
          '#required' => TRUE,
          '#default_value' => $this->contact_email,
        ],

        'contact_website' => [
          '#type' => 'url',
          '#title' => $this->t('Website URL', [], ['context' => 'DIPAS']),
          '#required' => FALSE,
          '#default_value' => $this->contact_website,
        ],

        'additional_info' => [
          '#type' => 'fieldset',
          '#title' => $this->t('Additonal information (will be undisclosed)', [], ['context' => 'DIPAS']),
          '#description' => $this->t('Some additional information on the project which will not be displayed on the frontend.', [], ['context' => 'DIPAS']),
          'data_responsible' => [
            '#type' => 'textfield',
            '#title' => $this->t('Data responsible organization', [], ['context' => 'DIPAS']),
            '#required' => FALSE,
            '#default_value' => $this->data_responsible,
          ],
        ],
      ],

      'logo_settings' => [
        '#type' => 'fieldset',
        '#title' => $this->t('Project Logos and images', [], ['context' => 'DIPAS']),
        '#description' => $this->t('Define the logos and images that are displayed on the website.', [], ['context' => 'DIPAS']),

        'project_logo' => [
          '#type' => 'select',
          '#options' => $this->getMediaOptions('logo'),
          '#default_value' => $this->project_logo,
          '#title' => $this->t('Project logo', [], ['context' => 'DIPAS']),
          '#description' => $this->t(
            'Select the media item that contains the project logo. If you need to create one first, click @here.',
            [
              '@here' => Link::fromTextAndUrl(
                $this->t('here', [], ['context' => 'DIPAS']),
                Url::fromRoute('entity.media.add_form', [
                  'media_type' => 'logo',
                  'destination' => Url::fromRoute('dipas.configform')->toString(),
                ])
              )->toString(),
            ],
            ['context' => 'DIPAS']
          ),
        ],

        'partner_logos' => [
          '#type' => 'fieldgroup',
          '#title' => $this->t('Partner logos', [], ['context' => 'DIPAS']),
          '#plugin' => 'ProjectInformation',
        ],
      ],

      'welcome_modal' => [
        '#type' => 'fieldset',
        '#title' => $this->t('Welcome Modal', [], ['context' => 'DIPAS']),

        'headline' => [
          '#type' => 'textfield',
          '#title' => $this->t('Headline', [], ['context' => 'DIPAS']),
          '#required' => TRUE,
          '#description' => $this->t('The headline of the welcome modal.', [], ['context' => 'DIPAS']),
          '#default_value' => $this->headline,
        ],

        'text' => [
          '#type' => 'textarea',
          '#title' => $this->t('Welcome Text', [], ['context' => 'DIPAS']),
          '#required' => FALSE,
          '#description' => $this->t('Define the text displayed within the welcome modal.', [], ['context' => 'DIPAS']),
          '#default_value' => $this->text,
        ],

        'project_image' => [
          '#type' => 'select',
          '#options' => $this->getMediaOptions('image'),
          '#title' => $this->t('Modal background image', [], ['context' => 'DIPAS']),
          '#description' => $this->t(
            'Select the media item that contains the project image. If you need to create one first, click @here.',
            [
              '@here' => Link::fromTextAndUrl(
                $this->t('here', [], ['context' => 'DIPAS']),
                Url::fromRoute('entity.media.add_form', [
                  'media_type' => 'image',
                  'destination' => Url::fromRoute('dipas.configform')->toString(),
                ])
              )->toString(),
            ],
            ['context' => 'DIPAS']
          ),
          '#default_value' => $this->project_image,
        ],

      ],

    ];

    $this->createMultivalueFormPortion(
      $section['logo_settings']['partner_logos'],
      'partner_logos',
      $form_state,
      $this->partner_logos ?: [],
      'No partner logos defined. Click the "Add logo" button to add a new logo.'
    );

    return $section;
  }

  /**
   * {@inheritdoc}
   */
  protected function getInputRow($property, $delta, array $row_defaults, FormStateInterface $form_state) {

    return [
      'partner_logo' => [
        '#type' => 'select',
        '#options' => $this->getMediaOptions('logo'),
        '#title' => $this->t('Partner logo', [], ['context' => 'DIPAS']),
        '#default_value' => !empty($row_defaults['partner_logo']) ? $row_defaults['partner_logo'] : '',
        '#inline' => TRUE,
        '#required' => TRUE,
      ],
      'logo_link' => [
        '#type' => 'url',
        '#title' => $this->t('Logo link', [], ['context' => 'DIPAS']),
        '#default_value' => !empty($row_defaults['logo_link']) ? $row_defaults['logo_link'] : '',
        '#inline' => TRUE,
      ],
    ];
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
  protected static function isSortable($property) {
    return TRUE;
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
  protected function getAddRowButtonTitle($property) {
    return 'Add logo';
  }

  /**
   * {@inheritdoc}
   */
  public static function getProcessedValues(array $plugin_values, array $form_values) {
    $partner_logos = self::getData('partner_logos', $plugin_values);
    array_walk($partner_logos, function (&$item) {
      $item['partner_logo'] = (int) $item['partner_logo'];
    });
    return [
      'site_name' => $plugin_values['site_settings']['site_name'],
      'site_email_name' => $plugin_values['site_settings']['site_email_name'],
      'site_email_address' => $plugin_values['site_settings']['site_email_address'],
      'department' => $plugin_values["project_contact"]["contact_details"]["organization"],
      'street1' => $plugin_values["project_contact"]["contact_details"]["address_line1"],
      'street2' => $plugin_values["project_contact"]["contact_details"]["address_line2"],
      'zip' => $plugin_values["project_contact"]["contact_details"]["postal_code"],
      'city' => $plugin_values["project_contact"]["contact_details"]["locality"],
      'contact_email' => $plugin_values["project_contact"]['contact_email'],
      'contact_telephone' => $plugin_values["project_contact"]['contact_telephone'],
      'contact_website' => $plugin_values["project_contact"]['contact_website'],
      'project_logo' => !empty($plugin_values['logo_settings']['project_logo']) ? (int) $plugin_values['logo_settings']['project_logo'] : '',
      'project_image' => !empty($plugin_values['welcome_modal']['project_image']) ? (int) $plugin_values['welcome_modal']['project_image'] : '',
      'partner_logos' => $partner_logos,
      'headline' => $plugin_values["welcome_modal"]['headline'],
      'text' => $plugin_values["welcome_modal"]['text'],
      'data_responsible' => $plugin_values["project_contact"]['additional_info']['data_responsible'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function onSubmit() {}

}
