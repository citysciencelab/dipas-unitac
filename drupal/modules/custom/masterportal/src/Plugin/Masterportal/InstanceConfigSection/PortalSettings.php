<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Plugin\Masterportal\InstanceConfigSection;

use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Form\FormStateInterface;
use Drupal\masterportal\PluginSystem\ControlPluginInterface;

/**
 * Defines a PortalSettings configuration section.
 *
 * @InstanceConfigSection(
 *   id = "PortalSettings",
 *   title = @Translation("Portal settings"),
 *   description = @Translation("Basic settings of the Masterportal."),
 *   sectionWeight = 5
 * )
 */
class PortalSettings extends InstanceConfigSectionBase {

  /**
   * The title of this Masterportal instance.
   *
   * @var string
   */
  protected $title;

  /**
   * Path to a custom logo file.
   *
   * @var string
   */
  protected $logo;

  /**
   * Link of the external website related to this instance.
   *
   * @var string
   */
  protected $link;

  /**
   * The tooltip shown when the mouse hovers above the title.
   *
   * @var string
   */
  protected $tooltip;

  /**
   * The active controls.
   *
   * @var array
   */
  protected $activePlugins;

  /**
   * Plugin settings of the active control plugins.
   *
   * @var array
   */
  protected $plugins;

  /**
   * Custom control plugin manager service.
   *
   * @var \Drupal\masterportal\PluginSystem\PluginManagerInterface
   */
  protected $controlPluginManager;

  /**
   * {@inheritdoc}
   */
  protected function setAdditionalDependencies(Container $container) {
    $this->controlPluginManager = $container->get('plugin.manager.masterportal.controls');
  }

  /**
   * {@inheritdoc}
   */
  public static function getDefaults() {
    return [
      'title' => 'Masterportal',
      'logo' => '{{library_path}}/img/Logo_Masterportal.svg',
      'link' => 'http://geoinfo.hamburg.de',
      'tooltip' => 'Landesbetrieb Geoinformation und Vermessung',
      'activePlugins' => ['Fullscreen', 'TotalView', '3D'],
      'plugins' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormSectionElements(FormStateInterface $form_state, array $settings, $pluginIdentifier) {

    // Determine the available control plugins.
    $available_control_plugins = $this->controlPluginManager->getPluginTypeOptions();

    $section = [
      'portalTitle' => [
        '#type' => 'fieldset',
        '#title' => $this->t('Portal title', [], ['context' => 'Masterportal']),

        'title' => [
          '#type' => 'textfield',
          '#title' => $this->t('The title of the Masterportal', [], ['context' => 'Masterportal']),
          '#default_value' => $this->title,
        ],

        'logo' => [
          '#type' => 'textfield',
          '#title' => $this->t('Path to a custom logo image', [], ['context' => 'Masterportal']),
          '#description' => $this->t(
            'Leave blank to omit a logo image. @availabletokens',
            ['@availabletokens' => $this->tokenService->availableTokens(['masterportal_instance'])],
            ['context' => 'Masterportal']
          ),
          '#default_value' => $this->logo,
          '#element_validate' => [[$this, 'validateFileExists']],
        ],

        'link' => [
          '#type' => 'url',
          '#title' => $this->t('External link', [], ['context' => 'Masterportal']),
          '#description' => $this->t('The external link associated with this instance (if any).', [], ['context' => 'Masterportal']),
          '#default_value' => $this->link,
        ],

        'tooltip' => [
          '#type' => 'textfield',
          '#title' => $this->t('Title tooltip', [], ['context' => 'Masterportal']),
          '#description' => $this->t('The tooltip that should be displayed when hovering above the title.', [], ['context' => 'Masterportal']),
          '#default_value' => $this->tooltip,
        ],

      ],

      'activePlugins' => [
        '#type' => 'checkboxes',
        '#title' => $this->t('Active control plugins', [], ['context' => 'Masterportal']),
        '#description' => $this->t('Select the controls you want to activate on this portal', [], ['context' => 'Masterportal']),
        '#options' => $available_control_plugins,
        '#default_value' => $this->activePlugins,
      ],
    ];

    // Add the Tool plugins.
    foreach (array_keys($available_control_plugins) as $controlPluginType) {

      // Get the plugin definition.
      $controlPluginDefinition = $this->controlPluginManager->getPluginDefinitions($controlPluginType);

      // Determine the plugin defaults.
      $pluginDefaults = isset($this->plugins[$controlPluginDefinition['class']])
        ? $this->plugins[$controlPluginDefinition['class']]
        : $controlPluginDefinition['class']::getDefaults();

      // Get an instance of this tool plugin.
      $controlPlugin = new $controlPluginDefinition['class']($pluginDefaults);

      // This is the actual selector for the dependant input.
      $dependantSelector = sprintf(
        ':input[name="settings[%s][activePlugins][%s]"]',
        $this->pluginDefinition["id"],
        $controlPluginType
      );

      // When a plugin form is available, integrate it as a subsection.
      if (($pluginform = $controlPlugin->getForm($form_state, $dependantSelector, 'checked', TRUE)) !== FALSE) {

        // Integrate this plugin's subsettings into the section container.
        $section[sprintf('details_%s', $controlPluginType)] = [
          '#type' => 'details',
          '#open' => TRUE,
          '#title' => $this->t('Plugin settings for plugin %plugin', ['%plugin' => $controlPluginDefinition["title"]], ['context' => 'Masterportal']),
          '#states' => [
            'invisible' => [$dependantSelector => ['checked' => FALSE]],
          ],
          'pluginsettings' => $pluginform,
        ];

      }

    }

    return $section;
  }

  /**
   * {@inheritdoc}
   */
  public function getSectionConfigArray(array $rawFormData, FormStateInterface $form_state) {
    $data = [
      'title' => !empty($rawFormData['portalTitle']['title']) ? $rawFormData['portalTitle']['title'] : '',
      'logo' => !empty($rawFormData['portalTitle']['logo']) ? $rawFormData['portalTitle']['logo'] : '',
      'link' => !empty($rawFormData['portalTitle']['link']) ? $rawFormData['portalTitle']['link'] : '',
      'tooltip' => !empty($rawFormData['portalTitle']['tooltip']) ? $rawFormData['portalTitle']['tooltip'] : '',
      'activePlugins' => array_keys(array_filter($rawFormData["activePlugins"])),
      'plugins' => [],
    ];

    // Collect setting data from enabled subplugins.
    foreach ($data['activePlugins'] as $pluginId) {

      // Get the plugin definition.
      $controlPluginDefinition = $this->controlPluginManager->getPluginDefinitions($pluginId);

      // Determine the plugin defaults.
      $pluginDefaults = isset($rawFormData[sprintf("details_%s", $pluginId)]["pluginsettings"])
        ? $rawFormData[sprintf("details_%s", $pluginId)]["pluginsettings"]
        : $controlPluginDefinition['class']::getDefaults();

      // Instantiate the activated plugin with actual configured data.
      /* @var \Drupal\masterportal\PluginSystem\PluginInterface $toolPlugin */
      $controlPlugin = new $controlPluginDefinition['class']($pluginDefaults);

      // Get the Plugin data.
      $pluginData = $controlPlugin->getConfigurationArray($form_state);

      // Store the plugin data along with the class to instantiate the plugin.
      $data['plugins'][$controlPluginDefinition['class']] = $pluginData;

    }

    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function injectSectionConfigurationSettings($type, \stdClass &$config) {

    // Common section configuration settings.
    switch ($type) {
      case 'config.js':
        break;

      case 'config.json':
        // Make sure the configuration section exists.
        static::ensureConfigPath($config, 'Portalconfig->[portalTitle, controls]');

        // Inject basic settings.
        foreach (['title', 'logo', 'link', 'toolTip'] as $property) {
          if (!empty($this->{$property})) {
            $config->Portalconfig->portalTitle->{$property} = $this->{$property};
          }
        }
        break;
    }

    // Inject the control plugins.
    foreach ($this->activePlugins as $pluginId) {

      // Get the plugin definition.
      /* @var array $pluginDefinition */
      $pluginDefinition = $this->controlPluginManager->getPluginDefinitions($pluginId);

      if (empty($this->plugins[$pluginDefinition['class']])) {
        switch ($type) {
          case 'config.js':
            if ($pluginDefinition['class']::hasJavascriptConfiguration()) {
              $config->{$pluginDefinition['configProperty']} = TRUE;
            }
            break;

          case 'config.json':
            if ($pluginDefinition['class']::hasJsonConfiguration()) {
              $config->Portalconfig->controls->{$pluginDefinition['configProperty']} = TRUE;
            }
            break;
        }
      }
      else {

        // Instantiate the plugin.
        /* @var ControlPluginInterface $plugin */
        $plugin = new $pluginDefinition['class']($this->plugins[$pluginDefinition['class']]);

        switch ($type) {
          case 'config.js':
            if ($pluginDefinition['class']::hasJavascriptConfiguration()) {
              $plugin->injectJavascriptConfiguration($config);
            }
            break;

          case 'config.json':
            // Inject the plugin configuration.
            if ($pluginDefinition['class']::hasJsonConfiguration()) {
              $config->Portalconfig->controls->{$pluginDefinition['configProperty']} = new \stdClass();
              $plugin->injectConfiguration($config->Portalconfig->controls->{$pluginDefinition['configProperty']});
            }
            break;
        }

      }

    }
  }

}
