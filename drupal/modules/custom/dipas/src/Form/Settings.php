<?php

namespace Drupal\dipas\Form;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\dipas\PluginSystem\SettingsSectionPluginManagerInterface;
use Drupal\masterportal\DomainAwareTrait;
use Drupal\masterportal\Exception\UnknownPluginMethodException;
use Drupal\masterportal\FindFormSectionTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Settings.
 *
 * @package Drupal\dipas\Form
 */
class Settings extends ConfigFormBase {

  use FindFormSectionTrait;
  use DomainAwareTrait;

  /**
   * Custom plugin manager service.
   *
   * @var \Drupal\dipas\PluginSystem\SettingsSectionPluginManagerInterface
   */
  protected $sectionPluginManager;

  /**
   * Custom logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Drupal's cache tags invalidator service.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheTagsInvalidator;

  /**
   * The currently logged-in user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('plugin.manager.dipas.setting_sections'),
      $container->get('logger.channel.dipas'),
      $container->get('cache_tags.invalidator'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    SettingsSectionPluginManagerInterface $section_plugin_manager,
    LoggerChannelInterface $logger,
    CacheTagsInvalidatorInterface $cache_tags_invalidator,
    AccountInterface $current_user
  ) {
    parent::__construct($config_factory);
    $this->sectionPluginManager = $section_plugin_manager;
    $this->logger = $logger;
    $this->cacheTagsInvalidator = $cache_tags_invalidator;
    $this->currentUser = $current_user;
  }

  /**
   * Returns the key of the configuration object.
   *
   * @return string
   */
  protected function getConfigName() {
    $configurationKey = drupal_static('dipas_config_key');
    if (is_null($configurationKey)) {
      $configurationKey = 'dipas.configuration';
      if ($this->isDomainModuleInstalled()) {
        $configurationKey = sprintf('dipas.%s.configuration', $this->getActiveDomain());
      }
    }
    return $configurationKey;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [$this->getConfigName()];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dipas.configuration';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config($this->getConfigName());

    $form['#tree'] = TRUE;

    $form['#attached']['library'] = ['dipas/configsection'];

    // A container to hold all the various setting containers.
    $form['settings'] = [
      '#theme_wrappers' => ['vertical_tabs'],
      'vertical_tabs' => [
        '#type' => 'vertical_tabs',
      ],
    ];

    // Collect the available configuration sections.
    $availableConfigSections = $this->sectionPluginManager->getPluginDefinitions();

    // Filter out sections to which the current user has no access to.
    $availableConfigSections = array_filter(
      $availableConfigSections,
      function (array $pluginDefinition) {
        return !isset($pluginDefinition['permissionRequired']) || $this->currentUser->hasPermission($pluginDefinition['permissionRequired']);
      }
    );

    // Add each available config section.
    foreach ($availableConfigSections as $pluginId => $configSection) {

      // Prepare the container holding the section definition.
      $form['settings'][$pluginId] = [
        '#type' => 'details',
        '#title' => $configSection['title'],
        '#description' => $configSection['description'],
        '#description_display' => 'before',
        '#weight' => $configSection['weight'],
        '#group' => 'vertical_tabs',
      ];

      // Determine the plugin default values.
      $pluginDefaults = !empty($config->get($pluginId))
        ? $config->get($pluginId)
        : $configSection["class"]::getDefaults();

      // Instantiate the plugin.
      /* @var \Drupal\dipas\PluginSystem\SettingsSectionPluginInterface $plugin */
      $plugin = new $configSection['class'](array_merge($pluginDefaults, ['_definition' => $configSection]));

      $form['settings'][$pluginId] = array_merge(
        $form['settings'][$pluginId],
        $plugin->getForm($form, $form_state)
      );

    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Get an editable instance of the settings.
    $config = $this->configFactory->getEditable($this->getConfigName());

    // Get the form values.
    $values = $form_state->getValues();

    // Process each section.
    foreach ($values['settings'] as $pluginId => $pluginValues) {

      // Skip over the vertical tabs entry.
      if ($pluginId === 'vertical_tabs') {
        continue;
      }

      // Get the plugin definition of the current section.
      $pluginDefinition = $this->sectionPluginManager->getPluginDefinitions($pluginId);

      // Determine the plugin default values.
      $pluginDefaults = !empty($config->get($pluginId))
        ? $config->get($pluginId)
        : $pluginDefinition["class"]::getDefaults();

      // Instantiate the plugin.
      /* @var \Drupal\dipas\PluginSystem\SettingsSectionPluginInterface $plugin */
      $plugin = new $pluginDefinition["class"](array_merge($pluginDefaults, ['_definition' => $pluginDefinition]));

      if ($plugin->hasConfigurationSettings()) {
        // Set the value in the configuration object.
        $config->set($pluginId, $plugin->getProcessedConfigurationValues($pluginValues, $values));
      }

    }

    // Save the Configuration.
    $config->save();

    // Flush drupal cache.
    $endpointsToInvalidate = array_map(
      function ($item) {
        return sprintf('dipasRestEndpoint:%s', $item);
      },
      [
        'init',
        'frontpage',
        'contributionmap',
        'contributionlist',
        'conceptionlist',
        'dataprivacy',
        'faq',
        'imprint',
        'projectinfo',
        'schedule',
        'statistics',
        'custompage',
      ]
    );
    $this->cacheTagsInvalidator->invalidateTags($endpointsToInvalidate);

    // Execute the dipas cron tasks.
    dipas_cron();

    parent::submitForm($form, $form_state);
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
   * @throws \Drupal\masterportal\Exception\UnknownPluginMethodException
   *   If a method called does not exist on the plugin that actually
   *   called it, this exception is thrown.
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *   If the plugin information given is invalid, this exception is thrown.
   *
   * @return mixed
   *   If the plugin returns any value, this is just passed on.
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
        [, $property] = explode(':', $trigger["#name"]);

        // Determine the form section that caused the method call.
        $section = $this->findSection($property, $form);

        // Do we have the information, which plugin actually triggered
        // the method call?
        if (!empty($section["#plugin"])) {

          // Determine the plugin data.
          $pluginDefinition = $this->sectionPluginManager->getPluginDefinitions($section["#plugin"]);

          $pluginDefaults = !empty($raw = $form_state->getValue(['settings', $section["#plugin"]]))
            ? $pluginDefinition['class']::getProcessedValues($raw, $form_state->getValues())
            : $pluginDefinition['class']::getDefaults();

          $plugin = new $pluginDefinition['class'](array_merge($pluginDefaults, ['_definition' => $pluginDefinition]));

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
        if (isset($trigger['#name'])) {
          throw new UnknownPluginMethodException('unknown', sprintf(
            'Call to undefined method %s in file %s, triggered by %s.',
            $method,
            __FILE__,
            (isset($trigger['#name']) ? $trigger['#name'] : 'unknown')
          ));
        }
    }
  }

}
