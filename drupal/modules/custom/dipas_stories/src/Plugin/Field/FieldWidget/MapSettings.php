<?php

namespace Drupal\dipas_stories\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Field\Annotation\FieldWidget;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\StringTextareaWidget;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\dipas_stories\PluginSystem\MasterportalSettingsSectionPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'masterportal_map_settings' widget.
 *
 * @FieldWidget(
 *   id = "masterportal_map_settings",
 *   label = @Translation("Masterportal Map Settings"),
 *   field_types = {
 *     "string_long"
 *   }
 * )
 */
class MapSettings extends StringTextareaWidget implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * @var \Drupal\dipas_stories\PluginSystem\MasterportalSettingsSectionPluginManager
   */
  protected $settingsSectionPluginManager;

  /**
   * @var \Drupal\dipas_stories\PluginSystem\MasterportalSettingsSectionPluginInterface[]
   */
  protected $plugins;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('logger.channel.dipas_stories'),
      $container->get('plugin.manager.dipas_stories.masterportal_settings_section')
    );
  }

  /**
   * Constructs a MapSettings object.
   *
   * @param string $plugin_id
   *   The plugin_id for the widget.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the widget is associated.
   * @param array $settings
   *   The widget settings.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   Custom logger channel
   * @param \Drupal\dipas_stories\PluginSystem\MasterportalSettingsSectionPluginManager
   *   Custom plugin manager
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    array $third_party_settings,
    LoggerChannelInterface $logger,
    MasterportalSettingsSectionPluginManager $settings_section_plugin_manager
  ) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->logger = $logger;
    $this->settingsSectionPluginManager = $settings_section_plugin_manager;

    $this->plugins = [];
    $pluginDefinitions = $this->settingsSectionPluginManager->getDefinitions();
    $names = [];
    $weights = [];
    foreach ($pluginDefinitions as $key => $plugin) {
      $names[$key] = strtolower((string) $plugin['title']);
      $weights[$key] = (int) $plugin['weight'];
    }
    array_multisort($weights, SORT_ASC, $names, SORT_STRING, $pluginDefinitions);

    foreach ($pluginDefinitions as $pluginID => $definition) {
      $this->plugins[$pluginID] = [
        'definition' => $definition,
        'instance' => new $definition['class']($this->getSetting('widgetMode')),
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'widgetMode' => 'story',
      'omittedWidgetSections' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return [
      'widgetMode' => [
        '#type' => 'select',
        '#title' => $this->t('Widget mode', [], ['context' => 'dipas_stories']),
        '#options' => [
          'story' => $this->t('Story', [], ['context' => 'dipas_stories']),
          'story_step' => $this->t('Story Step', [], ['context' => 'dipas_stories']),
        ],
        '#default_value' => $this->getSetting('widgetMode'),
      ],
      'omittedWidgetSections' => [
        '#type' => 'checkboxes',
        '#title' => $this->t('Omitted widget sections', [], ['context' => 'dipas_stories']),
        '#description' => $this->t('Should any sections be excluded from the widget?', [], ['context' => 'dipas_stories']),
        '#options' => array_combine(
          array_keys($this->plugins),
          array_map(
            function ($key) {
              return $this->plugins[$key]['definition']['title'];
            },
            array_keys($this->plugins)
          )
        ),
        '#default_value' => $this->getSetting('omittedWidgetSections'),
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $plugins = array_combine(
      array_keys($this->plugins),
      array_map(
        function ($key) {
          return $this->plugins[$key]['definition']['title'];
        },
        array_keys($this->plugins)
      )
    );

    $excludedSections = array_filter(
      $plugins,
      function ($key) {
        return in_array($key, $this->getSetting('omittedWidgetSections'));
      },
      ARRAY_FILTER_USE_KEY
    );

    return [
      $this->t(
        'Widget mode: @widgetMode',
        [
          '@widgetMode' => $this->getSetting('widgetMode') === 'story'
            ? 'Story'
            : 'Story Step'
        ],
        ['context' => 'dipas_stories']
      ),
      $this->t(
        'Excluded widget sections: @excludedSections',
        [
          '@excludedSections' => count($excludedSections)
            ? implode(',', $excludedSections)
            : $this->t('none', [], ['context' => 'dipas_stories_widget_sections']),
        ],
        ['context' => 'dipas_stories']
      )
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $deltaValues = !empty($items[$delta]->value) && ($decoded = json_decode($items[$delta]->value))
      ? (array) $decoded
      : FALSE;

    $fieldname = $this->fieldDefinition->getName();

    $element = $element + [
      '#tree' => TRUE,
      '#theme_wrappers' => ['horizontal_tabs'],
      'horizontal_tabs' => [
        '#type' => 'horizontal_tabs',
      ],
      '#attached' => [
        'library' => ['dipas_stories/mapsettings'],
        'drupalSettings' => [
          'dipas_stories' => [
            $fieldname => [
              'widgetMode' => $this->getSetting('widgetMode'),
            ],
          ],
        ],
      ],
    ];

    foreach ($this->plugins as $pluginID => $plugin) {
      if (in_array($pluginID, $this->getSetting('omittedWidgetSections'))) {
        continue;
      }

      $element[$pluginID] = [
        '#type' => 'details',
        '#title' => $plugin['definition']['title'],
        '#description' => $plugin['definition']['description'],
        '#description_display' => 'before',
        '#group' => 'horizontal_tabs',
      ];

      $element[$pluginID] = array_merge(
        $element[$pluginID],
        $plugin['instance']->formElements(
          $fieldname,
          $pluginID,
          $form_state,
          $delta,
          isset($deltaValues[$pluginID]) ? (array) $deltaValues[$pluginID] : [],
          (array) $deltaValues
        )
      );
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function extractFormValues(FieldItemListInterface $items, array $form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();

    // Extract the values from $form_state->getValues().
    $path = array_merge($form['#parents'], [$field_name]);
    $key_exists = NULL;
    $values = NestedArray::getValue($form_state->getValues(), $path, $key_exists);

    foreach ($values as &$deltaValues) {
      $pluginValues = [];

      foreach ($this->plugins as $pluginID => $plugin) {
        if (in_array($pluginID, $this->getSetting('omittedWidgetSections'))) {
          continue;
        }

        $pluginValues[$pluginID] = $plugin['instance']->massageFormValues($deltaValues[$pluginID], $form_state);
      }

      $deltaValues = json_encode($pluginValues);
    }

    $items->setValue($values);
  }

}
