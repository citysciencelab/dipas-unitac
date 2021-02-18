<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Plugin\Masterportal\SearchBar;

use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Form\FormStateInterface;
use Drupal\masterportal\PluginSystem\SearchBarPluginInterface;
use Drupal\masterportal\Plugin\Masterportal\PluginBase;

/**
 * Defines a SearchBar plugin implementation for BKG.
 *
 * @SearchBarPlugin(
 *   id = "BKG",
 *   title = @Translation("BKG - Federal Agency for Cartography and Geodesy"),
 *   description = @Translation("A search bar plugin to utilize the BKG search service."),
 *   configProperty = "bkg"
 * )
 */
class Bkg extends PluginBase implements SearchBarPluginInterface {

  /**
   * Custom service manager.
   *
   * @var \Drupal\masterportal\Service\ServiceManagerInterface
   */
  protected $serviceManager;

  const EPSG = 'EPSG:25832';

  /**
   * Service ID of the search service.
   *
   * @var string
   */
  protected $geosearchServiceId;

  /**
   * Service ID of the suggestions service.
   *
   * @var string
   */
  protected $suggestServiceId;

  /**
   * Score value for the search results.
   *
   * @var float
   */
  protected $score;

  /**
   * Number of suggestions.
   *
   * @var int
   */
  protected $suggestCount;

  /**
   * {@inheritdoc}
   */
  protected function setAdditionalDependencies(Container $container) {
    $this->serviceManager = $container->get('masterportal.servicesmanager');
  }

  /**
   * {@inheritdoc}
   */
  public static function getDefaults() {
    return [
      'geosearchServiceId' => '',
      'suggestServiceId' => '',
      'extent' => '[454591, 5809000, 700000, 6075769]',
      'score' => 0.6,
      'suggestCount' => 20,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(FormStateInterface $form_state, $dependantSelector = FALSE, $dependantSelectorProperty = NULL, $dependantSelectorValue = NULL) {
    return [
      'geosearchServiceId' => [
        '#type' => 'select',
        '#title' => $this->t('Service ID of the search service.', [], ['context' => 'Masterportal']),
        '#options' => $this->serviceManager->getServiceOptions(),
        '#empty_option' => $this->t('Please choose', [], ['context' => 'Masterportal']),
        '#default_value' => $this->geosearchServiceId,
        '#states' => [
          'required' => [$dependantSelector => [$dependantSelectorProperty => $dependantSelectorValue]],
        ],
      ],
      'suggestServiceId' => [
        '#type' => 'select',
        '#title' => $this->t('Service ID of the suggestions service.', [], ['context' => 'Masterportal']),
        '#options' => $this->serviceManager->getServiceOptions(),
        '#empty_option' => $this->t('Please choose', [], ['context' => 'Masterportal']),
        '#default_value' => $this->suggestServiceId,
        '#states' => [
          'required' => [$dependantSelector => [$dependantSelectorProperty => $dependantSelectorValue]],
        ],
      ],
      'extent' => [
        '#type' => 'textfield',
        '#title' => $this->t('Extent coordinates', [], ['context' => 'Masterportal']),
        '#description' => $this->t(
          "The @coordsystem coordinates, in which this search service performs it's search. Must be entered in a valid JSON format and contain an array with exactly 4 integers.",
          ['@coordsystem' => self::EPSG . ' (UTM)'],
          ['context' => 'Masterportal']
        ),
        '#default_value' => $this->extent,
        '#states' => ['required' => [$dependantSelector => [$dependantSelectorProperty => $dependantSelectorValue]]],
        '#element_validate' => [
          [$this, 'validateJsonInput'],
          [$this, 'validateExtent'],
        ],
      ],
      'score' => [
        '#type' => 'number',
        '#title' => $this->t('Score value for the search results.', [], ['context' => 'Masterportal']),
        '#min' => 0.1,
        '#max' => 5.0,
        '#step' => 0.1,
        '#default_value' => $this->score,
      ],
      'suggestCount' => [
        '#type' => 'number',
        '#title' => $this->t('Number of suggestions.', [], ['context' => 'Masterportal']),
        '#min' => 1,
        '#max' => 30,
        '#step' => 1,
        '#default_value' => $this->suggestCount,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigurationArray(FormStateInterface $form_state) {
    return [
      'geosearchServiceId' => (string) $this->geosearchServiceId,
      'suggestServiceId' => (string) $this->suggestServiceId,
      'extent' => (string) $this->extent,
      'score' => (float) $this->score,
      'suggestCount' => (int) $this->suggestCount,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function injectConfiguration(\stdClass &$pluginSection) {
    $pluginSection->espg = self::EPSG;
    $pluginSection->extent = json_decode($this->extent);
    $pluginSection->filter = 'filter=(typ:*)';
    $pluginSection->geosearchServiceId = $this->geosearchServiceId;
    $pluginSection->suggestServiceId = $this->suggestServiceId;
    $pluginSection->score = $this->score;
    $pluginSection->suggestCount = $this->suggestCount;
  }

}
