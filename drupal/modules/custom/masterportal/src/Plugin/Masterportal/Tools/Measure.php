<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Plugin\Masterportal\Tools;

use Drupal\Core\Form\FormStateInterface;
use Drupal\masterportal\Plugin\Masterportal\PluginBase;
use Drupal\masterportal\PluginSystem\ToolPluginInterface;

/**
 * Defines a tool plugin implementation for Measure.
 *
 * @ToolPlugin(
 *   id = "Measure",
 *   title = @Translation("Measure"),
 *   description = @Translation("A plugin that let's users measure distances and areas."),
 *   configProperty = "measure",
 *   isAddon = false
 * )
 */
class Measure extends PluginBase implements ToolPluginInterface {

  /**
   * The label of this plugin in the Masterportal.
   *
   * @var string
   */
  protected $name;

  /**
   * The icon this plugin uses in the UI.
   *
   * @var string
   */
  protected $glyphicon;

  /**
   * {@inheritdoc}
   */
  public static function getDefaults() {
    return [
      'name' => 'Strecke / Fläche messen',
      'glyphicon' => 'glyphicon-resize-full',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(FormStateInterface $form_state, $dependantSelector = FALSE, $dependantSelectorProperty = NULL, $dependantSelectorValue = NULL) {
    $states = [
      'required' => [$dependantSelector => [$dependantSelectorProperty => $dependantSelectorValue]],
    ];
    return [
      'name' => [
        '#type' => 'textfield',
        '#title' => $this->t('Name', [], ['context' => 'Masterportal']),
        '#default_value' => $this->name,
        '#states' => $states,
      ],
      'glyphicon' => $this->getGlyphiconSelect(
        $this->glyphicon,
        'Please choose',
        $states
      ),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigurationArray(FormStateInterface $form_state) {
    return [
      'name' => $this->name,
      'glyphicon' => $this->glyphicon,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function injectConfiguration(\stdClass &$pluginSection) {
    $pluginSection->name = $this->name;
    $pluginSection->glyphicon = $this->glyphicon;
  }

}
