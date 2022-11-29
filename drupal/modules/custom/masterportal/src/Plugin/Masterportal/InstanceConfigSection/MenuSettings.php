<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Plugin\Masterportal\InstanceConfigSection;

use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a MenuSettings configuration section.
 *
 * @InstanceConfigSection(
 *   id = "MenuSettings",
 *   title = @Translation("Menu settings"),
 *   description = @Translation("Settings related to the menu structure of the Masterportal."),
 *   sectionWeight = 20
 * )
 */
class MenuSettings extends InstanceConfigSectionBase {

  /**
   * Settings for the Tree.
   *
   * @var array
   */
  protected $tree;

  /**
   * Settings for the Tools.
   *
   * @var array
   */
  protected $tools;

  /**
   * Settings for the Filter.
   *
   * @var array
   */
  protected $filter;

  /**
   * Settings for the Legend.
   *
   * @var array
   */
  protected $legend;

  /**
   * {@inheritdoc}
   */
  public static function getDefaults() {
    return [
      'tree' => [
        'name' => 'Themen',
        'isInitOpen' => FALSE,
      ],
      'tools' => [
        'name' => 'Werkzeuge',
      ],
      'filter' => [
        'name' => 'Katgorieauswahl',
        'deactivateGFI' => FALSE,
        'active' => TRUE,
        'liveZoomToFeatures' => FALSE,
        'layerSelectorVisible' => FALSE,
        'isVisibleInMenu' => TRUE,
        'layers' => '[{"layerId": "contributions", "strategy": "active", "showHits": false, "snippetTags": false, "snippets": [{"type": "dropdown","attrName": "Thema","operator": "IN","display": "list","multiselect": true,"addSelectAll": false,"renderIcons": "fromLegend","info": false}]}]',
      ],
      'legend' => [
        'name' => 'Legende',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormSectionElements(FormStateInterface $form_state, array $settings, $pluginIdentifier) {

    // The section definition array.
    return [

      'tree' => [
        '#type' => 'details',
        '#open' => TRUE,
        '#title' => $this->t('Tree options', [], ['context' => 'Masterportal']),

        'name' => [
          '#type' => 'textfield',
          '#title' => $this->t('Name', [], ['context' => 'Masterportal']),
          '#default_value' => $this->tree['name'],
          '#required' => TRUE,
        ],

        'isInitOpen' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Open by default', [], ['context' => 'Masterportal']),
          '#description' => $this->t('Should the menu tree be initially opened?', [], ['context' => 'Masterportal']),
          '#default_value' => $this->tree['isInitOpen'],
        ],
      ],

      'tools' => [
        '#type' => 'details',
        '#open' => TRUE,
        '#title' => $this->t('Tools', [], ['context' => 'Masterportal']),

        'name' => [
          '#type' => 'textfield',
          '#title' => $this->t('Name', [], ['context' => 'Masterportal']),
          '#default_value' => $this->tools['name'],
          '#required' => TRUE,
        ],
      ],

      'filter' => [
        '#type' => 'details',
        '#open' => TRUE,
        '#title' => $this->t('Filter', [], ['context' => 'Masterportal']),

        'name' => [
          '#type' => 'textfield',
          '#title' => $this->t('Name', [], ['context' => 'Masterportal']),
          '#default_value' => $this->filter['name'],
          '#required' => TRUE,
        ],

        'deactivateGFI' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Deactivate GFI', [], ['context' => 'Masterportal']),
          '#default_value' => $this->filter['deactivateGFI'],
        ],

        'active' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Is active', [], ['context' => 'Masterportal']),
          '#default_value' => $this->filter['active'],
        ],

        'liveZoomToFeatures' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Live zoom to feature', [], ['context' => 'Masterportal']),
          '#default_value' => $this->filter['liveZoomToFeatures'],
        ],

        'layerSelectorVisible' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Display a selector for the layers?', [], ['context' => 'Masterportal']),
          '#default_value' => $this->filter['layerSelectorVisible'],
        ],

        'isVisibleInMenu' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Visible in menu?', [], ['context' => 'Masterportal']),
          '#default_value' => $this->filter['isVisibleInMenu'],
        ],

        'layers' => [
          '#type' => 'textarea',
          '#title' => $this->t('Predefined layers', [], ['context' => 'Masterportal']),
          '#default_value' => $this->filter['layers'],
          '#element_validate' => [[$this, 'validateJsonInput']],
          '#json_pretty_print' => TRUE,
        ],

      ],

      'legend' => [
        '#type' => 'details',
        '#open' => TRUE,
        '#title' => $this->t('Legend', [], ['context' => 'Masterportal']),

        'name' => [
          '#type' => 'textfield',
          '#title' => $this->t('Name', [], ['context' => 'Masterportal']),
          '#default_value' => $this->legend['name'],
          '#required' => TRUE,
        ],
      ],

    ];

  }

  /**
   * {@inheritdoc}
   */
  public function getSectionConfigArray(array $rawFormData, FormStateInterface $form_state) {

    // Prepare possible predefined layers (must be an array).
    $layers = !empty($rawFormData["filter"]["layers"])
      ? json_decode($rawFormData["filter"]["layers"])
      : [];

    if (!is_array($layers)) {
      $layers = [$layers];
    }

    // Collect basic form settings.
    return [
      'tree' => [
        'name' => $rawFormData["tree"]["name"],
        'isInitOpen' => (bool) $rawFormData["tree"]["isInitOpen"],
      ],
      'tools' => [
        'name' => $rawFormData["tools"]["name"],
      ],
      'filter' => [
        'name' => $rawFormData["filter"]["name"],
        'deactivateGFI' => (bool) $rawFormData["filter"]["deactivateGFI"],
        'active' => (bool) $rawFormData["filter"]["active"],
        'liveZoomToFeatures' => (bool) $rawFormData["filter"]["liveZoomToFeatures"],
        'layerSelectorVisible' => (bool) $rawFormData["filter"]["layerSelectorVisible"],
        'isVisibleInMenu' => (bool) $rawFormData["filter"]["isVisibleInMenu"],
        'layers' => json_encode($layers, JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT),
      ],
      'legend' => [
        'name' => $rawFormData["legend"]["name"],
      ],
    ];
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
        static::ensureConfigPath($config, 'Portalconfig->menu->[tree, tools, filter, legend]');

        // Inject the tree section.
        $config->Portalconfig->menu->tree->name = $this->tree["name"];
        $config->Portalconfig->menu->tree->isInitOpen = $this->tree["isInitOpen"];

        // Inject the tools section.
        $config->Portalconfig->menu->tools->name = $this->tools["name"];

        // Inject the filter section.
        $config->Portalconfig->menu->filter->name = $this->filter["name"];
        $config->Portalconfig->menu->filter->deactivateGFI = $this->filter["deactivateGFI"];
        $config->Portalconfig->menu->filter->active = $this->filter["active"];
        $config->Portalconfig->menu->filter->liveZoomToFeatures = $this->filter["liveZoomToFeatures"];
        $config->Portalconfig->menu->filter->layerSelectorVisible = $this->filter["layerSelectorVisible"];
        $config->Portalconfig->menu->filter->isVisibleInMenu = $this->filter["isVisibleInMenu"];
        $config->Portalconfig->menu->filter->layers = !empty($this->filter["layers"])
          ? json_decode($this->filter["layers"])
          : [];

        // Inject the legend section.
        $config->Portalconfig->menu->legend->name = $this->legend["name"];
        break;
    }
  }

}
