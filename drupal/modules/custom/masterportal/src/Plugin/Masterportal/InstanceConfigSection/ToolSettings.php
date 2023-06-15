<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Plugin\Masterportal\InstanceConfigSection;

use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a ToolSettings configuration section.
 *
 * @InstanceConfigSection(
 *   id = "ToolSettings",
 *   title = @Translation("Tool plugins"),
 *   description = @Translation("Settings related to the tools available in the Masterportal instance."),
 *   sectionWeight = 25
 * )
 */
class ToolSettings extends InstanceConfigSectionBase {

  /**
   * Custom tool plugin manager service.
   *
   * @var \Drupal\masterportal\PluginSystem\PluginManagerInterface
   */
  protected $toolPluginManager;

  /**
   * Active tool plugins.
   *
   * @var array
   */
  protected $activePlugins;

  /**
   * Plugin settings of the active tool plugins.
   *
   * @var array
   */
  protected $plugins;

  /**
   * {@inheritdoc}
   */
  protected function setAdditionalDependencies(Container $container) {
    $this->toolPluginManager = $container->get('plugin.manager.masterportal.tools');
  }

  /**
   * {@inheritdoc}
   */
  public static function getDefaults() {
    return [
      'activePlugins' => ['Gfi', 'Measure'],
      'plugins' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormSectionElements(FormStateInterface $form_state, array $settings, $pluginIdentifier) {
    // Determine the available search bar plugins.
    $available_tools_plugins = $this->toolPluginManager->getPluginTypeOptions();

    $section = [
      'activePlugins' => [
        '#type' => 'checkboxes',
        '#title' => $this->t('Enabled tool plugins', [], ['context' => 'Masterportal']),
        '#required' => FALSE,
        '#options' => $available_tools_plugins,
        '#default_value' => $this->activePlugins,
      ],
    ];

    // Add the Tool plugins.
    foreach (array_keys($available_tools_plugins) as $toolpluginType) {

      // Get the plugin definition.
      $toolPluginDefinition = $this->toolPluginManager->getPluginDefinitions((string) $toolpluginType);

      // Determine the plugin defaults.
      $pluginDefaults = isset($this->plugins[$toolPluginDefinition['class']])
        ? $this->plugins[$toolPluginDefinition['class']]
        : $toolPluginDefinition['class']::getDefaults();

      // Get an instance of this tool plugin.
      $toolPlugin = new $toolPluginDefinition['class']($pluginDefaults);

      // This is the actual selector for the dependant input.
      $dependantSelector = sprintf(
        ':input[name="settings[%s][activePlugins][%s]"]',
        $this->pluginDefinition["id"],
        $toolpluginType
      );

      // When a plugin form is available, integrate it as a subsection.
      if (($pluginform = $toolPlugin->getForm($form_state, $dependantSelector, 'checked', TRUE)) !== FALSE) {

        // Integrate this plugin's subsettings into the section container.
        $section[sprintf('details_%s', $toolpluginType)] = [
          '#type' => 'details',
          '#open' => TRUE,
          '#title' => $this->t('Plugin settings for plugin %plugin', ['%plugin' => $toolPluginDefinition["title"]], ['context' => 'Masterportal']),
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
    // Collect basic form settings.
    $data = [
      'activePlugins' => array_keys(array_filter($rawFormData["activePlugins"])),
      'plugins' => [],
    ];

    // Collect setting data from enabled subplugins.
    foreach ($data['activePlugins'] as $pluginId) {

      // Get the plugin definition.
      $toolPluginDefinition = $this->toolPluginManager->getPluginDefinitions($pluginId);

      // Determine the plugin defaults.
      $pluginDefaults = isset($rawFormData[sprintf("details_%s", $pluginId)]["pluginsettings"])
        ? $rawFormData[sprintf("details_%s", $pluginId)]["pluginsettings"]
        : $toolPluginDefinition['class']::getDefaults();

      // Instantiate the activated plugin with actual configured data.
      /* @var \Drupal\masterportal\PluginSystem\PluginInterface $toolPlugin */
      $toolPlugin = new $toolPluginDefinition['class']($pluginDefaults);

      // Get the Plugin data.
      $pluginData = $toolPlugin->getConfigurationArray($form_state);

      // Store the plugin data along with the class to instantiate the plugin.
      $data['plugins'][$toolPluginDefinition['class']] = $pluginData;

    }

    // Return the completed data.
    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function injectSectionConfigurationSettings($type, \stdClass &$config) {
    switch ($type) {
      case 'config.js':

        foreach ($this->activePlugins as $pluginId) {
          // Get the plugin definition.
          $pluginDefinition = $this->toolPluginManager->getPluginDefinitions($pluginId);

          // Add the plugin to the addons if needed
          if ($pluginDefinition['isAddon']) {

            if (!isset($config->addons)) {
              $config->addons = [];
            }

            $config->addons[] = $pluginDefinition['configProperty'];
          }
        }

        break;

      case 'config.json':
        // Make sure the configuration section exists.
        static::ensureConfigPath($config, 'Portalconfig->menu->tools->children');

        // Inject the tool plugins.
        foreach ($this->activePlugins as $pluginId) {

          // Get the plugin definition.
          $pluginDefinition = $this->toolPluginManager->getPluginDefinitions($pluginId);

          // Determine the plugin defaults.
          $pluginDefaults = !empty($this->plugins[$pluginDefinition['class']])
            ? $this->plugins[$pluginDefinition['class']]
            : $pluginDefinition['class']::getDefaults();

          // Instantiate the plugin.
          $plugin = new $pluginDefinition['class']($pluginDefaults);

          // Inject the plugin configuration.
          $config->Portalconfig->menu->tools->children->{$pluginDefinition['configProperty']} = new \stdClass();
          $plugin->injectConfiguration($config->Portalconfig->menu->tools->children->{$pluginDefinition['configProperty']});

        }
        break;
    }
  }

}
