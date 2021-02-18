<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Plugin\Masterportal\InstanceConfigSection;

use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a DesignSettings configuration section.
 *
 * @InstanceConfigSection(
 *   id = "DesignSettings",
 *   title = @Translation("Design settings"),
 *   description = @Translation("Settings related to the design of the Masterportal."),
 *   sectionWeight = 10
 * )
 */
class DesignSettings extends InstanceConfigSectionBase {

  /**
   * The configured UI style.
   *
   * @var string
   */
  protected $uiStyle;

  /**
   * The configured tree type.
   *
   * @var string
   */
  protected $treeType;

  /**
   * The configured scale line setting.
   *
   * @var bool
   */
  protected $scaleLine;

  /**
   * Use simpleMap URLs or not?
   *
   * @var bool
   */
  protected $simpleMap;

  /**
   * Path to the background image.
   *
   * @var string
   */
  protected $backgroundImage;

  /**
   * {@inheritdoc}
   */
  public static function getDefaults() {
    return [
      'uiStyle' => 'default',
      'treeType' => 'default',
      'scaleLine' => FALSE,
      'simpleMap' => FALSE,
      'backgroundImage' => '{{library_path}}/img/backgroundCanvas.jpeg',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormSectionElements(FormStateInterface $form_state) {

    $section = [
      'uiStyle' => [
        '#type' => 'select',
        '#title' => $this->t('UI style', [], ['context' => 'Masterportal']),
        '#description' => $this->t('Choose the style of the map when it is integrated.', [], ['context' => 'Masterportal']),
        '#options' => $this->entity->getUiStyleLabel(),
        '#default_value' => $this->uiStyle,
        '#required' => TRUE,
      ],

      'treeType' => [
        '#type' => 'select',
        '#title' => $this->t('Tree type', [], ['context' => 'Masterportal']),
        '#description' => $this->t('Choose the style of the menu tree.', [], ['context' => 'Masterportal']),
        '#options' => [
          'default' => $this->t('Default', [], ['context' => 'Masterportal']),
          'light' => $this->t('Light', [], ['context' => 'Masterportal']),
          'custom' => $this->t('Custom', [], ['context' => 'Masterportal']),
        ],
        '#default_value' => $this->treeType,
        '#required' => TRUE,
      ],

      'scaleLine' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Scale line', [], ['context' => 'Masterportal']),
        '#description' => $this->t('Should a scale line be integrated?', [], ['context' => 'Masterportal']),
        '#default_value' => $this->scaleLine,
      ],

      'simpleMap' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Add simpleMap links?', [], ['context' => 'Masterportal']),
        '#description' => $this->t('Should a simpleMap URL be added to the "Save selection" dialog? (Not available on tree type "light")', [], ['context' => 'Masterportal']),
        '#default_value' => $this->simpleMap,
        '#states' => [
          'disabled' => [
            'select[name="settings[DesignSettings][treeType]"]' => ['value' => 'light'],
          ],
          'unchecked' => [
            'select[name="settings[DesignSettings][treeType]"]' => ['value' => 'light'],
          ],
        ],
      ],

      'backgroundImage' => [
        '#type' => 'textfield',
        '#title' => $this->t('Path to a custom background image', [], ['context' => 'Masterportal']),
        '#description' => $this->t(
          'Leave blank if no custom background image should be used. @availabletokens',
          ['@availabletokens' => $this->tokenService->availableTokens(['masterportal_instance'])],
          ['context' => 'Masterportal']
        ),
        '#default_value' => $this->backgroundImage,
        '#element_validate' => [[$this, 'validateFileExists']],
      ],

    ];

    return $section;
  }

  /**
   * {@inheritdoc}
   */
  public function getSectionConfigArray(array $rawFormData, FormStateInterface $form_state) {
    $data = [];
    foreach (array_keys(self::getDefaults()) as $property) {
      $data[$property] = $rawFormData[$property];
    }
    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function injectSectionConfigurationSettings($type, \stdClass &$config) {
    switch ($type) {
      case 'config.js':
        $config->simpleMap = $this->simpleMap;
        if ($this->uiStyle !== 'default') {
          $config->uiStyle = $this->uiStyle;
        }
        else {
          unset($config->uiStyle);
        }
        break;

      case 'config.json':
        // Make sure the configuration section exists.
        static::ensureConfigPath($config, 'Portalconfig->mapView');

        // Basic settings.
        $config->Portalconfig->treeType = $this->treeType;
        $config->Portalconfig->scaleLine = $this->scaleLine;

        // mapView settings.
        if (!empty($this->backgroundImage)) {
          $config->Portalconfig->mapView->backgroundImage = $this->backgroundImage;
        }
        break;
    }
  }

}
