<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\SettingsSection;

use Drupal\Component\DependencyInjection\Container;
use Drupal\Core\Form\FormStateInterface;
use Drupal\masterportal\Form\MultivalueRowTrait;

/**
 * Class SidebarSettings.
 *
 * @SettingsSection(
 *   id = "SidebarSettings",
 *   title = @Translation("Sidebar settings"),
 *   description = @Translation("Settings related to the sidebar displayed in the website."),
 *   weight = 30,
 *   affectedConfig = {}
 * )
 *
 * @package Drupal\dipas\Plugin\SettingsSection
 */
class SidebarSettings extends SettingsSectionBase {

  use MultivalueRowTrait;

  /**
   * Custom plugin manager service.
   *
   * @var \Drupal\dipas\PluginSystem\SidebarBlockPluginManagerInterface
   */
  protected $sidebarBlockPluginManager;

  /**
   * {@inheritdoc}
   */
  protected function setAdditionalDependencies(Container $container) {
    $this->sidebarBlockPluginManager = $container->get('plugin.manager.dipas.sidebar_blocks');
  }


  /**
   * {@inheritdoc}
   */
  public static function getDefaults() {
    return [
      'blocks' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array $form, FormStateInterface $form_state) {

    // Get all available block plugins.
    $availableBlockPlugins = $this->sidebarBlockPluginManager->getPluginDefinitions();

    // Inject the information, if the current plugin was enabled as well as potential settings.
    array_walk(
      $availableBlockPlugins,
      function (&$plugin) {
        $plugin['enabled'] = in_array($plugin['id'], array_keys($this->blocks));
        $plugin['settings'] = $plugin['enabled'] ? $this->blocks[$plugin['id']] : [];
      }
    );

    // Sort enabled plugins by configuration, disabled plugins alphabetically at the bottom.
    $names = [];
    $deltas = [];
    $availableBlockPlugins = array_values($availableBlockPlugins);
    foreach ($availableBlockPlugins as $index => $plugin) {
      $names[$index] = $plugin['name'];
      $deltas[$index] = in_array($plugin['id'], array_keys($this->blocks))
        ? array_search($plugin['id'], array_keys($this->blocks))
        : 9999;
    }
    array_multisort($deltas, SORT_NUMERIC, $names, SORT_ASC, $availableBlockPlugins);

    $form = [
      'blocks' => [
        '#type' => 'fieldgroup',
        '#title' => $this->t('Sidebar blocks', [], ['context' => 'DIPAS']),
        '#description' => $this->t('Check the blocks you want to show on the website. Sort by drag and drop.', [], ['context' => 'DIPAS']),
        '#plugin' => 'SidebarSettings',
      ],
    ];

    $this->createMultivalueFormPortion(
      $form['blocks'],
      'blocks',
      $form_state,
      $availableBlockPlugins,
      'No available block plugins.'
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function getInputRow($property, $delta, array $row_defaults, FormStateInterface $form_state) {
    // Get the plugin definition of the current sidebar plugin.
    $plugindefinition = $this->sidebarBlockPluginManager->getPluginDefinitions($row_defaults['id']);

    // Determine the plugin default settings (if any).
    $defaults = !empty($row_defaults['settings'])
      ? $row_defaults['settings']
      : $plugindefinition['class']::getDefaultSettings();

    // Instantiate the plugin.
    /* @var \Drupal\dipas\PluginSystem\SidebarBlockPluginInterface $plugin */
    $plugin = new $plugindefinition['class']($defaults);

    // Build the form row.
    $row = [
      'pluginid' => [
        '#type' => 'value',
        '#value' => $row_defaults['id'],
      ],
      'enabled' => [
        '#type' => 'checkbox',
        '#title' => $row_defaults['name'],
        '#description' => $row_defaults['description'],
        '#default_value' => $row_defaults['enabled'],
        '#attributes' => [
          'data-plugin' => $row_defaults['id'],
        ],
      ],
    ];

    // If the plugin provides settings, include them in the row.
    $checkboxSelector = sprintf(':input[data-plugin="%s"]', $row_defaults['id']);
    if ($settings = $plugin->getSettingsForm($checkboxSelector)) {
      $row['pluginsettings'] = [
        '#type' => 'fieldgroup',
        '#states' => [
          'invisible' => [$checkboxSelector => ['checked' => FALSE]],
        ],
      ] + $settings;
    }

    // Return the ready-built row.
    return $row;
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
  protected function canRowsBeRemoved($property) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  protected function canRowsBeAdded($property) {
    return FALSE;
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
  public function onSubmit() {}

  /**
   * {@inheritdoc}
   */
  public static function getProcessedValues(array $plugin_values, array $form_values) {
    $sidebar_blocks = self::getData('blocks', $plugin_values);
    $sectionsettings = ['blocks' => []];
    foreach ($sidebar_blocks as $block) {
      if ($block['enabled']) {
        $sectionsettings['blocks'][$block['pluginid']] = isset($block['pluginsettings']) ? $block['pluginsettings'] : [];
      }
    }
    return $sectionsettings;
  }

}
