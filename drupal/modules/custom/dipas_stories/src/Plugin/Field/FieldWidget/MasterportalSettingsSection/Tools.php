<?php

namespace Drupal\dipas_stories\Plugin\Field\FieldWidget\MasterportalSettingsSection;

use Drupal\Core\Form\FormStateInterface;
use Drupal\dipas_stories\Annotation\MasterportalSettingsSection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a MasterportalSettingsSection plugin implementation for tool settings.
 *
 * @MasterportalSettingsSection(
 *   id = "Tools",
 *   title = @Translation("Tools"),
 *   description = @Translation("Choose and configure tool plugins for the DIPAS story Masterportal"),
 *   weight = 4
 * )
 */
class Tools extends PluginBase {

  /**
   * @var \Drupal\masterportal\PluginSystem\PluginManagerInterface
   */
  protected $toolPluginManager;

  /**
   * @var array
   */
  protected $toolPluginOptions;

  /**
   * {@inheritdoc}
   */
  protected function setAdditionalDependencies(ContainerInterface $container) {
    $this->toolPluginManager = $container->get('plugin.manager.masterportal.tools');

    $availableToolPlugins = array_filter(
      $this->toolPluginManager->getPluginDefinitions(),
      function ($pluginID) {
        return !in_array($pluginID, ['DipasStorySelector', 'DipasStoryLoader']);
      },
      ARRAY_FILTER_USE_KEY
    );

    foreach ($availableToolPlugins as $definition) {
      $this->toolPluginOptions[$definition['configProperty']] = $definition['title']->__toString();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function formElements($fieldname, $pluginID, FormStateInterface $form_state, $delta, array $pluginValues, array $fieldValue) {
    $element = [];

    if ($this->widgetMode === 'story') {
      $element['selectedToolPlugins'] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('Available tool plugins', [], ['context' => 'dipas_stories']),
        '#options' => $this->toolPluginOptions,
        '#default_value' => $pluginValues ? array_keys($pluginValues) : [],
      ];

      foreach ($this->toolPluginOptions as $configProperty => $definition) {
        // Get the plugin definition.
        $toolPluginDefinition = $this->toolPluginManager->getPluginDefinitionByConfigProperty($configProperty);

        // Determine the plugin defaults.
        $pluginDefaults = $pluginValues && in_array($configProperty, array_keys($pluginValues))
          ? $pluginValues[$configProperty]
          : $toolPluginDefinition['class']::getDefaults();

        // Get an instance of this tool plugin.
        /* @var \Drupal\masterportal\PluginSystem\ToolPluginInterface $toolPlugin */
        $toolPlugin = new $toolPluginDefinition['class']((array) $pluginDefaults);

        // This is the actual selector for the dependant input.
        $dependantSelector = sprintf(
          ':input[name*="[%d][Tools][selectedToolPlugins][%s]"]',
          $delta,
          $configProperty
        );

        // When a plugin form is available, integrate it as a subsection.
        if (($pluginform = $toolPlugin->getForm($form_state, $dependantSelector, 'checked', TRUE)) !== FALSE) {
          // Integrate this plugin's subsettings into the section container.
          $element[sprintf('details_%s', $configProperty)] = [
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
    }
    else {
      /* @var \Drupal\Core\Entity\ContentEntityInterface $entity */
      $editedEntity = $form_state->getformObject()->getEntity();

      $mapsettings = $this->getMapSettingsFromStory($editedEntity->getEntityTypeId(), $editedEntity->id());
      $selectedTools = $mapsettings ? (array) $mapsettings->Tools : [];

      $element = [
        'activeToolPlugin' => [
          '#type' => 'radios',
          '#title' => $this->t('Select active tool plugin for this step', [], ['context' => 'dipas_stories']),
          '#description' => $this->t('The selected tool plugin will automatically be set to an active, usable state when displaying this story step', [], ['context' => 'dipas_stories']),
          '#options' => array_merge(
            ['none' => $this->t('No automatic tool activation', [], ['context' => 'dipas_stories'])],
            array_combine(
              array_keys($selectedTools),
              array_map(
                function ($key) use ($selectedTools) {
                  return $selectedTools[$key]->name;
                },
                array_keys($selectedTools)
              )
            )
          ),
          '#default_value' => $pluginValues['activeToolPlugin'] ?? 'none',
        ],
      ];
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $rawValues, FormStateInterface $form_state) {
    if ($this->widgetMode === 'story') {
      $selectedToolPlugins = array_filter($rawValues['selectedToolPlugins']);
      return array_combine(
        $selectedToolPlugins,
        array_map(
          function ($configProperty) use ($rawValues) {
            return $rawValues[sprintf('details_%s', $configProperty)]['pluginsettings'];
          },
          $selectedToolPlugins
        )
      );
    }
    else {
      return $rawValues;
    }
  }

}
