<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Plugin\Masterportal\InstanceConfigSection;

use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a SearchBarSettings configuration section.
 *
 * @InstanceConfigSection(
 *   id = "SearchBarSettings",
 *   title = @Translation("Search bar settings"),
 *   description = @Translation("Settings related to the structure and functionality of the search bar of the Masterportal."),
 *   sectionWeight = 30
 * )
 */
class SearchBarSettings extends InstanceConfigSectionBase {

  /**
   * Minimum chars to initiate a search.
   *
   * @var int
   */
  protected $minChars;

  /**
   * Zoom level after a search result has been selected.
   *
   * @var int
   */
  protected $zoomLevel;

  /**
   * The number of items in the recommendations list.
   *
   * @var int
   */
  protected $recommendedListlenth;

  /**
   * Placeholder string for the search field.
   *
   * @var string
   */
  protected $placeholder;

  /**
   * Quick help toggle.
   *
   * @var bool
   */
  protected $quickHelp;

  /**
   * HTML ID of the DOM element to which the search results gets attached.
   *
   * @var string
   */
  protected $renderToDOM;

  /**
   * {@inheritdoc}
   */
  public static function getDefaults() {
    return [
      'minChars' => 3,
      'zoomLevel' => 9,
      'recommendedListlenth' => 5,
      'placeholder' => 'Suche nach Adresse/Thema',
      'quickHelp' => FALSE,
      'renderToDOM' => '#searchbarInMap',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormSectionElements(FormStateInterface $form_state) {
    return [
      'minChars' => [
        '#type' => 'number',
        '#title' => $this->t('Minimum characters', [], ['context' => 'Masterportal']),
        '#description' => $this->t('The minimum number of characters that have to be entered to perform a search.', [], ['context' => 'Masterportal']),
        '#min' => 1,
        '#max' => 10,
        '#step' => 1,
        '#default_value' => $this->minChars,
      ],
      'zoomLevel' => [
        '#type' => 'range',
        '#title' => $this->t('Zoom level', [], ['context' => 'Masterportal']),
        '#description' => $this->t('The zoom level to set when a search result is chosen.', [], ['context' => 'Masterportal']),
        '#min' => 1,
        '#max' => 15,
        '#step' => 1,
        '#default_value' => $this->zoomLevel,
      ],
      'recommendedListlenth' => [
        '#type' => 'number',
        '#title' => $this->t('Recommendations', [], ['context' => 'Masterportal']),
        '#description' => $this->t('The number of items in the recommendations list.', [], ['context' => 'Masterportal']),
        '#min' => 1,
        '#max' => 10,
        '#step' => 1,
        '#default_value' => $this->recommendedListlenth,
      ],
      'quickHelp' => [
        '#type' => 'checkbox',
        '#title' => $this->t('QuickHelp', [], ['context' => 'Masterportal']),
        '#description' => $this->t('Should quick help tips get offered?', [], ['context' => 'Masterportal']),
        '#default_value' => $this->quickHelp,
      ],
      'placeholder' => [
        '#type' => 'textfield',
        '#title' => $this->t('Placeholder', [], ['context' => 'Masterportal']),
        '#description' => $this->t('The placeholder for the search bar field.', [], ['context' => 'Masterportal']),
        '#default_value' => $this->placeholder,
      ],
      'renderToDOM' => [
        '#type' => 'textfield',
        '#title' => $this->t('DOM ID', [], ['context' => 'Masterportal']),
        '#description' => $this->t('HTML ID of the DOM element to which the search results gets attached.', [], ['context' => 'Masterportal']),
        '#default_value' => $this->renderToDOM,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getSectionConfigArray(array $rawFormData, FormStateInterface $form_state) {
    return [
      'minChars' => (int) $rawFormData["minChars"],
      'zoomLevel' => (int) $rawFormData["zoomLevel"],
      'recommendedListlenth' => (int) $rawFormData["recommendedListlenth"],
      'placeholder' => (string) $rawFormData["placeholder"],
      'renderToDOM' => (string) $rawFormData["renderToDOM"],
      'quickHelp' => (bool) $rawFormData["quickHelp"],
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
        static::ensureConfigPath($config, 'Portalconfig->searchBar');

        // Inject basic settings.
        $config->Portalconfig->searchBar->minChars = $this->minChars;
        $config->Portalconfig->searchBar->zoomLevel = $this->zoomLevel;
        $config->Portalconfig->searchBar->recommendedListlenth = $this->recommendedListlenth;
        $config->Portalconfig->searchBar->quickHelp = $this->quickHelp;
        $config->Portalconfig->searchBar->placeholder = $this->placeholder;
        $config->Portalconfig->searchBar->renderToDOM = $this->renderToDOM;
        break;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function hasPostCompositionHook() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function postCompositionHook($type, \stdClass &$config) {
    switch ($type) {
      case 'config.js':
        break;

      case 'config.json':
        // Mix in the minChars setting to all enabled search plugins.
        foreach ($config->Portalconfig->searchBar as $key => &$section) {
          // Ignore all own properties.
          if (isset($this->{$key})) {
            continue;
          }
          $section->minChars = $this->minChars;
        }
        break;
    }
  }

}
