<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Plugin\Masterportal\InstanceConfigSection;

use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a SearchPluginSettings configuration section.
 *
 * @InstanceConfigSection(
 *   id = "SearchPluginSettings",
 *   title = @Translation("Search plugins"),
 *   description = @Translation("Select the search components you want to use in this Masterportal instance."),
 *   sectionWeight = 35
 * )
 */
class SearchPluginSettings extends InstanceConfigSectionBase {

  /**
   * Custom search bar plugin manager service.
   *
   * @var \Drupal\masterportal\PluginSystem\PluginManagerInterface
   */
  protected $searchPluginManager;

  /**
   * Active search plugins.
   *
   * @var array
   */
  protected $activePlugins;

  /**
   * Plugin settings of the active search plugins.
   *
   * @var array
   */
  protected $plugins;

  /**
   * {@inheritdoc}
   */
  protected function setAdditionalDependencies(Container $container) {
    $this->searchPluginManager = $container->get('plugin.manager.masterportal.search_bar');
  }

  /**
   * {@inheritdoc}
   */
  public static function getDefaults() {
    return [
      'activePlugins' => ['Tree', 'VisibleVector'],
      'plugins' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormSectionElements(FormStateInterface $form_state) {
    // Determine the available search bar plugins.
    $available_searchbar_plugins = $this->searchPluginManager->getPluginTypeOptions();

    $section = [
      'activePlugins' => [
        '#type' => 'checkboxes',
        '#title' => $this->t('Enabled search plugins', [], ['context' => 'Masterportal']),
        '#required' => FALSE,
        '#options' => $available_searchbar_plugins,
        '#default_value' => $this->activePlugins,
      ],
    ];

    // Integrate potential subsettings for enabled search plugins.
    foreach ($available_searchbar_plugins as $searchPluginType => $searchPluginTitle) {

      // This is the actual selector for the dependant input.
      $dependantSelector = sprintf(
        ':input[name="settings[%s][activePlugins][%s]"]',
        $this->pluginDefinition["id"],
        $searchPluginType
      );

      // Get the plugin definition for the plugin in question.
      $pluginDefinition = $this->searchPluginManager->getPluginDefinitions($searchPluginType);

      // Is there a previous configuration present?
      $plugindefaults = !empty($this->plugins[$pluginDefinition["class"]])
        ? $this->plugins[$pluginDefinition["class"]]
        : $pluginDefinition["class"]::getDefaults();

      // Get an actual instance of the plugin.
      /* @var \Drupal\masterportal\PluginSystem\SearchBarPluginInterface $searchbarPlugin */
      $searchbarPlugin = new $pluginDefinition["class"]($plugindefaults);

      // When a plugin form is available, integrate it as a subsection.
      if (($pluginform = $searchbarPlugin->getForm($form_state, $dependantSelector, 'checked', TRUE)) !== FALSE) {

        // Integrate this plugin's subsettings into the section container.
        $section[sprintf('details_%s', $searchPluginType)] = [
          '#type' => 'details',
          '#open' => TRUE,
          '#title' => $this->t('Plugin settings for plugin %plugin', ['%plugin' => $pluginDefinition["title"]], ['context' => 'Masterportal']),
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
      $searchPluginDefinition = $this->searchPluginManager->getPluginDefinitions($pluginId);

      // Determine the plugin defaults.
      $pluginDefaults = isset($rawFormData[sprintf("details_%s", $pluginId)]["pluginsettings"])
        ? $rawFormData[sprintf("details_%s", $pluginId)]["pluginsettings"]
        : $searchPluginDefinition['class']::getDefaults();

      // Instantiate the activated plugin with actual configured data.
      /* @var \Drupal\masterportal\PluginSystem\SearchBarPluginInterface $searchPlugin */
      $searchPlugin = new $searchPluginDefinition['class']($pluginDefaults);

      // Get the Plugin data.
      $pluginData = $searchPlugin->getConfigurationArray($form_state);

      // Store the plugin data along with the class to instantiate the plugin.
      $data['plugins'][$searchPluginDefinition['class']] = $pluginData;

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
        break;

      case 'config.json':
        // Make sure the configuration section exists.
        static::ensureConfigPath($config, 'Portalconfig->searchBar');

        // Inject the tool plugins.
        foreach ($this->activePlugins as $pluginId) {

          // Get the plugin definition.
          $pluginDefinition = $this->searchPluginManager->getPluginDefinitions($pluginId);

          // Determine the plugin defaults.
          $pluginDefaults = !empty($this->plugins[$pluginDefinition['class']])
            ? $this->plugins[$pluginDefinition['class']]
            : $pluginDefinition['class']::getDefaults();

          // Instantiate the plugin.
          $plugin = new $pluginDefinition['class']($pluginDefaults);

          // Inject the plugin configuration.
          $config->Portalconfig->searchBar->{$pluginDefinition['configProperty']} = new \stdClass();
          $plugin->injectConfiguration($config->Portalconfig->searchBar->{$pluginDefinition['configProperty']});

        }
        break;
    }
  }

}
