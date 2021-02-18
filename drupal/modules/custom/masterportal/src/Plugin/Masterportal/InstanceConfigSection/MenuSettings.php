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
        'glyphicon' => 'glyphicon-list',
        'isInitOpen' => FALSE,
      ],
      'tools' => [
        'name' => 'Werkzeuge',
        'glyphicon' => 'glyphicon-wrench',
      ],
      'filter' => [
        'name' => 'Katgorieauswahl',
        'glyphicon' => 'glyphicon-filter',
        'deactivateGFI' => FALSE,
        'isGeneric' => FALSE,
        'isInitOpen' => FALSE,
        'liveZoomToFeatures' => FALSE,
        'allowMultipleQueriesPerLayer' => FALSE,
        'isVisibleInMenu' => TRUE,
        'predefinedQueries' => '{"layerId": "beteiligungsfeatures", "isActive": true, "isSelected": true, "name": "Alle", "liveZoomToFeatures": false, "attributeWhiteList": ["Thema"], "snippetType": "checkbox-classic"}',
      ],
      'legend' => [
        'name' => 'Legende',
        'glyphicon' => 'glyphicon-book',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormSectionElements(FormStateInterface $form_state) {

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

        'glyphicon' => $this->getGlyphiconSelect($this->tree['glyphicon']),

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

        'glyphicon' => $this->getGlyphiconSelect($this->tools['glyphicon']),
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

        'glyphicon' => $this->getGlyphiconSelect($this->filter['glyphicon']),

        'deactivateGFI' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Deactivate GFI', [], ['context' => 'Masterportal']),
          '#default_value' => $this->filter['deactivateGFI'],
        ],

        'isGeneric' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Is generic', [], ['context' => 'Masterportal']),
          '#default_value' => $this->filter['isGeneric'],
        ],

        'isInitOpen' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Initially opened?', [], ['context' => 'Masterportal']),
          '#default_value' => $this->filter['isInitOpen'],
        ],

        'liveZoomToFeatures' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Live zoom to feature', [], ['context' => 'Masterportal']),
          '#default_value' => $this->filter['liveZoomToFeatures'],
        ],

        'allowMultipleQueriesPerLayer' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Allow multiple queries per layer?', [], ['context' => 'Masterportal']),
          '#default_value' => $this->filter['allowMultipleQueriesPerLayer'],
        ],

        'isVisibleInMenu' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Visible in menu?', [], ['context' => 'Masterportal']),
          '#default_value' => $this->filter['isVisibleInMenu'],
        ],

        'predefinedQueries' => [
          '#type' => 'textarea',
          '#title' => $this->t('Predefined queries', [], ['context' => 'Masterportal']),
          '#default_value' => $this->filter['predefinedQueries'],
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

        'glyphicon' => $this->getGlyphiconSelect($this->legend['glyphicon']),
      ],

    ];

  }

  /**
   * {@inheritdoc}
   */
  public function getSectionConfigArray(array $rawFormData, FormStateInterface $form_state) {

    // Prepare possible predefined queries (must be an array).
    $predefinedQueries = !empty($rawFormData["filter"]["predefinedQueries"])
      ? json_decode($rawFormData["filter"]["predefinedQueries"])
      : [];

    if (!is_array($predefinedQueries)) {
      $predefinedQueries = [$predefinedQueries];
    }

    // Collect basic form settings.
    return [
      'tree' => [
        'name' => $rawFormData["tree"]["name"],
        'glyphicon' => $rawFormData["tree"]["glyphicon"],
        'isInitOpen' => (bool) $rawFormData["tree"]["isInitOpen"],
      ],
      'tools' => [
        'name' => $rawFormData["tools"]["name"],
        'glyphicon' => $rawFormData["tools"]["glyphicon"],
      ],
      'filter' => [
        'name' => $rawFormData["filter"]["name"],
        'glyphicon' => $rawFormData["filter"]["glyphicon"],
        'deactivateGFI' => (bool) $rawFormData["filter"]["deactivateGFI"],
        'isGeneric' => (bool) $rawFormData["filter"]["isGeneric"],
        'isInitOpen' => (bool) $rawFormData["filter"]["isInitOpen"],
        'liveZoomToFeatures' => (bool) $rawFormData["filter"]["liveZoomToFeatures"],
        'allowMultipleQueriesPerLayer' => (bool) $rawFormData["filter"]["allowMultipleQueriesPerLayer"],
        'isVisibleInMenu' => (bool) $rawFormData["filter"]["isVisibleInMenu"],
        'predefinedQueries' => json_encode($predefinedQueries, JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT),
      ],
      'legend' => [
        'name' => $rawFormData["legend"]["name"],
        'glyphicon' => $rawFormData["legend"]["glyphicon"],
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
        $config->Portalconfig->menu->tree->glyphicon = $this->tree["glyphicon"];
        $config->Portalconfig->menu->tree->isInitOpen = $this->tree["isInitOpen"];

        // Inject the tools section.
        $config->Portalconfig->menu->tools->name = $this->tools["name"];
        $config->Portalconfig->menu->tools->glyphicon = $this->tools["glyphicon"];

        // Inject the filter section.
        $config->Portalconfig->menu->filter->name = $this->filter["name"];
        $config->Portalconfig->menu->filter->glyphicon = $this->filter["glyphicon"];
        $config->Portalconfig->menu->filter->deactivateGFI = $this->filter["deactivateGFI"];
        $config->Portalconfig->menu->filter->isGeneric = $this->filter["isGeneric"];
        $config->Portalconfig->menu->filter->isInitOpen = $this->filter["isInitOpen"];
        $config->Portalconfig->menu->filter->liveZoomToFeatures = $this->filter["liveZoomToFeatures"];
        $config->Portalconfig->menu->filter->allowMultipleQueriesPerLayer = $this->filter["allowMultipleQueriesPerLayer"];
        $config->Portalconfig->menu->filter->isVisibleInMenu = $this->filter["isVisibleInMenu"];
        $config->Portalconfig->menu->filter->predefinedQueries = !empty($this->filter["predefinedQueries"])
          ? json_decode($this->filter["predefinedQueries"])
          : [];

        // Inject the legend section.
        $config->Portalconfig->menu->legend->name = $this->legend["name"];
        $config->Portalconfig->menu->legend->glyphicon = $this->legend["glyphicon"];
        break;
    }
  }

}
