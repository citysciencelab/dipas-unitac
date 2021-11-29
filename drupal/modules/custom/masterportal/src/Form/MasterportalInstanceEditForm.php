<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html
 *   GPL-2.0-or-later
 */

namespace Drupal\masterportal\Form;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\masterportal\DomainAwareTrait;
use Drupal\masterportal\FindFormSectionTrait;
use Drupal\masterportal\PluginSystem\PluginManagerInterface;
use Drupal\masterportal\Service\Masterportal;
use Drupal\masterportal\Exception\UnknownPluginMethodException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class MasterportalInstanceEditForm.
 *
 * Contains the form definition to create or edit
 * Masterportal instance configurations.
 *
 * @package Drupal\masterportal\Form
 */
class MasterportalInstanceEditForm extends EntityForm {

  use FindFormSectionTrait, DomainAwareTrait;

  /**
   * Drupal's service container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

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
   * The custom storage for "masterportal_instance" entities.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $entityStorage;

  /**
   * Drupal's cache tags invalidator service.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheTagsInvalidator;

  /**
   * Custom config section plugin manager.
   *
   * @var \Drupal\masterportal\PluginSystem\PluginManagerInterface
   */
  protected $configSectionPluginManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container,
      $container->get('logger.channel.masterportal'),
      $container->get('current_user'),
      $container->get('entity_type.manager'),
      $container->get('cache_tags.invalidator'),
      $container->get('plugin.manager.masterportal.instance_config_section'),
      $container->get('module_handler')
    );
  }

  /**
   * MasterportalInstanceEditForm constructor.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   The custom logger channel for this module.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The user accessing this form.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Drupal's entity type manager service.
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cache_tags_invalidator
   *   Drupal's cache tags invalidator service.
   * @param \Drupal\masterportal\PluginSystem\PluginManagerInterface $config_section_manager
   *   Custom plugin manager service for config section plugins.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *   Thrown if the entity type doesn't exist.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   *   Thrown if the storage handler couldn't be loaded.
   */
  public function __construct(
    ContainerInterface $container,
    LoggerChannelInterface $logger,
    AccountInterface $current_user,
    EntityTypeManagerInterface $entity_type_manager,
    CacheTagsInvalidatorInterface $cache_tags_invalidator,
    PluginManagerInterface $config_section_manager
  ) {
    $this->container = $container;
    $this->logger = $logger;
    $this->currentUser = $current_user;
    $this->entityStorage = $entity_type_manager->getStorage('masterportal_instance');
    $this->cacheTagsInvalidator = $cache_tags_invalidator;
    $this->configSectionPluginManager = $config_section_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

    /*
     * Deny editing of the config entity for all that don't have the
     * "administer site configuration" permission.
     */
    if ($this->entity->id() == 'config' && !$this->currentUser->hasPermission('administer site configuration')) {
      throw new HttpException(403, 'Unauthorized');
    }

    // We're dealing with nested values.
    $form['#tree'] = TRUE;

    // The administrative label of this instance configuration.
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Administrative label', [], ['context' => 'Masterportal']),
      '#default_value' => $this->entity->label(),
      '#maxlength' => 255,
      '#required' => TRUE,
    ];

    // The ID of this entity (autogenerated by default).
    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $this->entity->getName(),
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#field_prefix' => $this->getActiveDomain() . '.',
      '#machine_name' => [
        'exists' => [$this, 'exists'],
        'source' => ['label'],
      ],
    ];

    // The ID of this entity (autogenerated by default).
    $form['hidden'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Hidden', [], ['context' => 'Masterportal']),
      '#description' => $this->t('Should this instance be hidden on the instances page?', [], ['context' => 'Masterportal']),
      '#default_value' => $this->entity->get('hidden'),
    ];

    // Add a hint that everything except the layers is pre-configured to a
    // default and does not need to be adjusted (unless other acceptance
    // criteria exist).
    if ($this->entity->isNew()) {
      $form['configurationHint'] = [
        '#type' => 'container',
        '#attributes' => [
          'style' => 'padding: 10px 20px; background-color: #eeeeee; border: solid 2px #aaaaaa; border-radius: 7px;',
        ],
        'headline' => [
          '#type' => 'html_tag',
          '#tag' => 'h3',
          '#value' => $this->t('Detailed configuration section', [], ['context' => 'Masterportal']),
        ],

        'hint' => [
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#value' => $this->t('Everything <b>except the map layers</b> is pre-configured in this instance and does not need to be set. Only change settings that need to match your requirements.', [], ['context' => 'Masterportal']),
        ],
      ];
    }

    // A container to hold all the various setting containers.
    $form['settings'] = [
      '#theme_wrappers' => ['vertical_tabs'],
      'vertical_tabs' => [
        '#type' => 'vertical_tabs',
      ],
    ];

    // Get the settings variable.
    $instanceSettings = $this->entity->get('settings');

    // Collect the available configuration sections.
    $availableConfigSections = $this->configSectionPluginManager->getPluginDefinitions();

    // Add each available config section.
    foreach ($availableConfigSections as $pluginId => $configSection) {

      // Prepare the container holding the section definition.
      $form['settings'][$pluginId] = [
        '#type' => 'details',
        '#title' => $configSection['title'],
        '#description' => $configSection['description'],
        '#weight' => $configSection['sectionWeight'],
        '#group' => 'vertical_tabs',
        '#plugin' => sprintf('%s/%s', $this->configSectionPluginManager->getPluginType(), $pluginId),
      ];

      // Determine the plugin default values.
      $pluginDefaults = isset($instanceSettings[$pluginId])
        ? $instanceSettings[$pluginId]
        : $configSection["class"]::getDefaults();

      // Instantiate the plugin.
      /* @var \Drupal\masterportal\PluginSystem\InstanceConfigSectionInterface $plugin */
      $plugin = new $configSection['class'](array_merge(
        $pluginDefaults,
        ['_entity' => $this->entity, '_definition' => $configSection]
      ));

      $form['settings'][$pluginId] = array_merge(
        $form['settings'][$pluginId],
        $plugin->getFormSectionElements($form_state, !empty($instanceSettings[$pluginId]) ? $instanceSettings[$pluginId] : [])
      );

    }

    // Return the form.
    return parent::form($form, $form_state);

  }

  /**
   * Determines if the chosen id already exists.
   *
   * @param string $id
   *   The ID to validate.
   *
   * @return bool
   *   TRUE if the vocabulary exists, FALSE otherwise.
   */
  public function exists($id) {
    $instanceId = $this->getActiveDomain() . '.' . $id;
    return !empty($this->entityStorage->load($instanceId));
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    return [
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Save changes', [], ['context' => 'Masterportal']),
        '#submit' => [
          [$this, 'save'],
        ],
      ],
      'cancel' => [
        '#type' => 'link',
        '#title' => $this->t('Cancel', [], ['context' => 'Masterportal']),
        '#attributes' => ['class' => ['button']],
        '#url' => new Url('masterportal.settings.instances'),
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {

    // Create or update?
    $is_new = $this->entity->isNew();

    // Basic entity information.
    $instanceId = $this->getActiveDomain() . '.' . $form_state->getValue('id');
    $this->entity->set('domain', $this->getActiveDomain());
    $this->entity->set('id', $instanceId);
    $this->entity->set('instance_name', $form_state->getValue('id'));
    $this->entity->set('label', $form_state->getValue('label'));
    $this->entity->set('hidden', (bool) $form_state->getValue('hidden'));

    // Determine the configured settings.
    $form_settings = $form_state->getValue('settings');

    // Initialize the settings container that holds all settings.
    $defaults = !empty($this->entity->get('settings'))
      ? $this->entity->get('settings')
      : [];

    // Delegate the handling of the entity values to the
    // respective section plugins.
    $availableConfigSections = $this->configSectionPluginManager->getPluginDefinitions();

    // Iterate over the available config section plugins.
    foreach ($availableConfigSections as $pluginId => $configSection) {

      // Determine the plugin default values.
      $pluginDefaults = isset($defaults[$pluginId])
        ? $defaults[$pluginId]
        : $configSection["class"]::getDefaults();

      // Instantiate the plugin with the currently stored data.
      /* @var \Drupal\masterportal\PluginSystem\InstanceConfigSectionInterface $plugin */
      $plugin = new $configSection['class'](array_merge(
        $pluginDefaults,
        ['_entity' => $this->entity, '_definition' => $configSection]
      ));

      // Get the new configuration data.
      if (!empty($pluginData = $plugin->getSectionConfigArray($form_settings[$pluginId], $form_state))) {
        $settings[$pluginId] = $pluginData;
      }

    }

    // Set the value on the entity.
    $this->entity->set('settings', $settings);

    // Save the entity.
    $this->entity->save();

    // Since the settings made on this entity can also affect basic
    // configuration settings, we need to invalidate the cache tags
    // for the BasicSettings as well.
    $cacheTagsToInvalidate = [sprintf('%s:BasicSettings', Masterportal::CACHE_ID_PREFIX)];

    // Plus, we'll invalidate all cache tags related
    // to this instance configuration.
    $cacheTagsToInvalidate[] = sprintf('%s:instance:%s', Masterportal::CACHE_ID_PREFIX, $this->entity->id());

    // Invalidate the tags collected.
    $this->cacheTagsInvalidator->invalidateTags($cacheTagsToInvalidate);

    // Log the operation.
    $this->logger->notice(
      'Masterportal instance configuration %name @operation by user %user.',
      [
        '%name' => $this->entity->label(),
        '@operation' => $this->t($is_new ? 'created' : 'updated', [], ['context' => 'Masterportal']),
        '%user' => $this->currentUser->getDisplayName(),
      ]
    );

    // Set a confirmation notice.
    \Drupal::messenger()->addMessage(
      $this->t(
        'Instance %instance was successfully %action.',
        [
          '%instance' => $this->entity->label(),
          '%action' => $is_new
            ? $this->t('created', [], ['context' => 'Masterportal'])
            : $this->t('updated', [], ['context' => 'Masterportal']),
        ]
      )
    );

    // Redirect back to the list view.
    $form_state->setRedirect('masterportal.settings.instances');

  }

  /**
   * Magic function to determine the source of plugin methods.
   *
   * Plugins can implement form functions, but do not act as the form instance
   * themselves. Since the callback of Drupal gets to the form and not to the
   * plugin, we need to determine which plugin triggered the call and
   * instantiate it.
   *
   * @param string $method
   *   The name of the function called.
   * @param array $arguments
   *   Array of arguments passed to that functions.
   *
   * @return mixed
   *   If the plugin returns any value, this is just passed on.
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *   If the plugin information given is invalid, this exception is thrown.
   *
   * @throws \Drupal\masterportal\Exception\UnknownPluginMethodException
   *   If a method called does not exist on the plugin that actually
   *   called it, this exception is thrown.
   */
  public function __call($method, array $arguments) {
    switch ($method) {

      // Methods of the MultivalueRowTrait trait.
      case 'addRow':
      case 'removeRow':
      case 'multivalueAjaxCallback':
        [$form, $form_state] = $arguments;
        /* @var array $form */
        /* @var FormStateInterface $form_state */
        $trigger = $form_state->getTriggeringElement();
        [, $property,] = explode(':', $trigger["#name"]);

        // Determine the form section that caused the method call.
        $section = $this->findSection($property, $form);

        // Do we have the information, which plugin actually triggered
        // the method call?
        if (!empty($section["#plugin"])) {

          // Determine the plugin that caused the call.
          [$pluginType, $pluginID] = explode('/', $section["#plugin"]);

          // Determine the plugin data.
          $plugin = $this->getPlugin($pluginType, $pluginID);

          // Check if the callback exists.
          if (!method_exists($plugin, $method)) {
            $this->logger->error(
              'Call to undefined method %method on plugin %plugin.',
              [
                '%method' => $method,
                '%plugin' => $section["#plugin"],
              ]
            );
            throw new UnknownPluginMethodException($section["#plugin"], sprintf(
              'Call to undefined method %s on plugin %s.',
              $method,
              $section["#plugin"]
            ));
          }

          // Make sure we stop here, even if the plugin does not
          // generate any output.
          if ($return = $plugin->{$method}($form, $form_state)) {
            return $return;
          }
          else {
            break;
          }

        }

      /*
       * We do not know which plugin it was. The section property '#plugin'
       * is missing. So we simply omit to break this switch/case here and
       * let this method run into it's default logger.
       */

      default:
        $this->logger->error(
          'Call to undefined method %method in file %file, triggered by %trigger.',
          [
            '%method' => $method,
            '%file' => __FILE__,
            '%trigger' => isset($trigger['#name']) ? $trigger['#name'] : 'unknown',
          ]
        );
        throw new UnknownPluginMethodException('unknown', sprintf(
          'Call to undefined method %s in file %s, triggered by %s.',
          $method,
          __FILE__,
          (isset($trigger['#name']) ? $trigger['#name'] : 'unknown')
        ));
    }
  }

  /**
   * Finds and instantiates a plugin.
   *
   * @param string $pluginType
   *   The plugin type id.
   * @param string $pluginID
   *   The plugin ID.
   *
   * @return object
   *   The instantiated plugin.
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *   If no plugin definition can be found, this exception is thrown.
   *
   */
  private function getPlugin($pluginType, $pluginID) {
    switch ($pluginType) {

      case 'instance_config_section':
        $plugindefinitions = $this->configSectionPluginManager->getPluginDefinitions();
        break;

      case 'searchbar_plugin':
        $pluginManager = $this->container->get('plugin.manager.masterportal.search_bar');
        $plugindefinitions = $pluginManager->getPluginDefinitions();
        break;

    }

    // Plugin definitions have been found for the given plugin type.
    if (isset($plugindefinitions[$pluginID])) {
      // Instantiate and return the plugin.
      return new $plugindefinitions[$pluginID]['class']([]);
    }
    else {
      throw new PluginNotFoundException(
        sprintf('%s/%s', $pluginType, $pluginID),
        'No handling routine defined for given plugin string!'
      );
    }
  }

}
