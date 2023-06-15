<?php

namespace Drupal\dipas_stories\Plugin\Masterportal\Tools;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\masterportal\Annotation\ToolPlugin;
use Drupal\masterportal\Plugin\Masterportal\PluginBase;
use Drupal\masterportal\PluginSystem\ToolPluginInterface;

/**
 * Defines a tool plugin implementation for the DIPAS Story Loader plugin.
 *
 * @ToolPlugin(
 *   id = "DipasStoryLoader",
 *   title = @Translation("Story Telling Tool"),
 *   description = @Translation("Present engaging stories with geographic references in an interactive way."),
 *   configProperty = "dataNarrator",
 *   isAddon = true
 * )
 */
class DipasStoryLoader extends PluginBase implements ToolPluginInterface {

  use StringTranslationTrait;

  /**
   * @var bool
   */
  protected $autoplay;

  /**
   * @var string
   */
  protected $parameterName;

  /**
   * @var integer
   */
  protected $initialWidth;

  /**
   * @var integer
   */
  protected $initialWidthMobile;

  /**
   * {@inheritdoc}
   */
  public static function getDefaults() {
    return [
      'autoplay' => TRUE,
      'parameterName' => 'story',
      'initialWidth' => 0.25,
      'initialWidthMobile' => 0.25,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(FormStateInterface $form_state, $dependantSelector = FALSE, $dependantSelectorProperty = NULL, $dependantSelectorValue = NULL) {
    return [
      'autoplay' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Autoplay referenced story?', [], ['context' => 'dipas_stories']),
        '#default_value' => $this->autoplay,
      ],
      'parameterName' => [
        '#type' => 'textfield',
        '#title' => $this->t('Name of the GET-Parameter containing the URL to the Story JSON', [], ['context' => 'dipas_stories']),
        '#default_value' => $this->parameterName,
        '#states' => [
          'invisible' => [
            'input[name=settings\[ToolSettings\]\[details_DipasStoryLoader\]\[pluginsettings\]\[autoplay\]]' => ['checked' => FALSE],
          ],
          'required' => [
            'input[name=settings\[ToolSettings\]\[details_DipasStoryLoader\]\[pluginsettings\]\[autoplay\]]' => ['checked' => TRUE],
            [$dependantSelector => [$dependantSelectorProperty => $dependantSelectorValue]],
          ],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigurationArray(FormStateInterface $form_state) {
    return [
      'autoplay' => $this->autoplay,
      'parameterName' => $this->parameterName,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function injectConfiguration(\stdClass &$pluginSection) {
    $pluginSection->name = $this->t('Story Loader', [], ['context' => 'dipas_stories']);
    $pluginSection->icon = 'bi-bookshelf';
    $pluginSection->renderToWindow = FALSE;
    $pluginSection->autoplay = $this->autoplay;
    $pluginSection->parameterName = $this->parameterName;
    $pluginSection->active = TRUE;
    $pluginSection->initialWidth = $this->initialWidth;
    $pluginSection->initialWidthMobile = $this->initialWidthMobile;
  }

}
