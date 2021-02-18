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
 * Defines a SearchBar plugin implementation for Gazetteer.
 *
 * @SearchBarPlugin(
 *   id = "Gazetteer",
 *   title = @Translation("Gazetteer"),
 *   description = @Translation("A search bar plugin to utilize the Gazetteer search service."),
 *   configProperty = "gazetteer"
 * )
 */
class Gazetteer extends PluginBase implements SearchBarPluginInterface {

  /**
   * Custom service manager.
   *
   * @var \Drupal\masterportal\Service\ServiceManagerInterface
   */
  protected $serviceManager;

  /**
   * The service ID of the gazetteer search service.
   *
   * @var string
   */
  protected $serviceId;

  /**
   * Flag indicating if street names should get searched.
   *
   * @var bool
   */
  protected $searchStreets;

  /**
   * Flag indicating if stret names including house numbers should get searched.
   *
   * @var bool
   */
  protected $searchHouseNumbers;

  /**
   * Flag indicating if district names should get searched.
   *
   * @var bool
   */
  protected $searchDistricts;

  /**
   * Flag indicating if parcel names should get searched.
   *
   * @var bool
   */
  protected $searchParcels;

  /**
   * Flag indicating if street keys should get searched.
   *
   * @var bool
   */
  protected $searchStreetKey;

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
      'serviceId' => '',
      'searchStreets' => FALSE,
      'searchHouseNumbers' => FALSE,
      'searchDistricts' => FALSE,
      'searchParcels' => FALSE,
      'searchStreetKey' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(FormStateInterface $form_state, $dependantSelector = FALSE, $dependantSelectorProperty = NULL, $dependantSelectorValue = NULL) {
    return [
      'serviceId' => [
        '#type' => 'select',
        '#title' => $this->t('Service', [], ['context' => 'Masterportal']),
        '#options' => $this->serviceManager->getServiceOptions(),
        '#empty_option' => $this->t('Please choose', [], ['context' => 'Masterportal']),
        '#default_value' => $this->serviceId,
        '#states' => [
          'required' => [$dependantSelector => [$dependantSelectorProperty => $dependantSelectorValue]],
        ],
      ],
      'searchStreets' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Search for matching street names', [], ['context' => 'Masterportal']),
        '#default_value' => $this->searchStreets,
      ],
      'searchHouseNumbers' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Search for matching street names including house numbers', [], ['context' => 'Masterportal']),
        '#default_value' => $this->searchHouseNumbers,
        '#attributes' => ['class' => ['dependee']],
        '#states' => [
          'disabled' => [
            ':input[type="checkbox"][name*="Gazetteer][pluginsettings][searchStreets"]' => ['checked' => FALSE],
          ],
          'unchecked' => [
            ':input[type="checkbox"][name*="Gazetteer][pluginsettings][searchStreets"]' => ['checked' => FALSE],
          ],
        ],
      ],
      'searchDistricts' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Search for matching district names', [], ['context' => 'Masterportal']),
        '#default_value' => $this->searchDistricts,
      ],
      'searchParcels' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Search for matching parcel names', [], ['context' => 'Masterportal']),
        '#default_value' => $this->searchParcels,
      ],
      'searchStreetKey' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Search for matching street keys', [], ['context' => 'Masterportal']),
        '#default_value' => $this->searchStreetKey,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigurationArray(FormStateInterface $form_state) {
    return [
      'serviceId' => (string) $this->serviceId,
      'searchStreets' => (bool) $this->searchStreets,
      'searchHouseNumbers' => (bool) $this->searchHouseNumbers,
      'searchDistricts' => (bool) $this->searchDistricts,
      'searchParcels' => (bool) $this->searchParcels,
      'searchStreetKey' => (bool) $this->searchStreetKey,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function injectConfiguration(\stdClass &$pluginSection) {
    $pluginSection->serviceId = $this->serviceId;
    $pluginSection->searchStreets = $this->searchStreets;
    $pluginSection->searchHouseNumbers = $this->searchHouseNumbers;
    $pluginSection->searchDistricts = $this->searchDistricts;
    $pluginSection->searchParcels = $this->searchParcels;
    $pluginSection->searchStreetKey = $this->searchStreetKey;
  }

}
