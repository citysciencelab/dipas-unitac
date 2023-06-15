<?php

namespace Drupal\dipas_stories\Plugin\Masterportal\Tools;

use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Form\FormStateInterface;
use Drupal\masterportal\Annotation\ToolPlugin;
use Drupal\masterportal\Plugin\Masterportal\PluginBase;
use Drupal\masterportal\PluginSystem\ToolPluginInterface;

/**
 * Defines a tool plugin implementation for the DIPAS story selector tool.
 *
 * @ToolPlugin(
 *   id = "DipasStorySelector",
 *   title = @Translation("DIPAS Story Selector"),
 *   description = @Translation("Displays available stories for the Story Telling Tool in a sidebar."),
 *   configProperty = "dipasStorySelector",
 *   isAddon = true
 * )
 */
class DipasStorySelector extends PluginBase implements ToolPluginInterface {

  /**
   * @var string
   */
  protected $storyIndexURL;

  /**
   * @var \GuzzleHttp\ClientInterface
   */
  protected $guzzle;

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
  protected function setAdditionalDependencies(Container $container) {
    parent::setAdditionalDependencies($container);
    $this->guzzle = $container->get('http_client');
  }

  /**
   * {@inheritdoc}
   */
  public static function getDefaults() {
    return [
      'storyIndexURL' => '',
      'initialWidth' => 0.25,
      'initialWidthMobile' => 0.25,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(FormStateInterface $form_state, $dependantSelector = FALSE, $dependantSelectorProperty = NULL, $dependantSelectorValue = NULL) {
    return [
      'storyIndexURL' => [
        '#type' => 'textfield',
        '#title' => $this->t('Story overview JSON', [], ['context' => 'dipas_stories']),
        '#description' => $this->t('URL of the resource providing the story overview JSON', [], ['context' => 'dipas_stories']),
        '#default_value' => $this->storyIndexURL,
        '#element_validate' => [[$this, 'checkStoryOverviewJSON']],
        '#states' => [
          'required' => [[$dependantSelector => [$dependantSelectorProperty => $dependantSelectorValue]]],
        ],

      ],
    ];
  }

  /**
   * Custom field validator.
   *
   * @param array $element
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param array $complete_form
   *
   * @return void
   */
  public function checkStoryOverviewJSON(array &$element, FormStateInterface $form_state, array &$complete_form) {
    if ($form_state->getValue('settings')['ToolSettings']['activePlugins']['DipasStorySelector']) {
      if (!preg_match('~^https?://~', $element['#value'])) {
        $form_state->setError(
          $element,
          $this->t(
            'Tool-Plugin "DIPAS Story Selector": The value of the URL to the Story overview JSON must be a valid URL starting with http(s)!',
            [],
            ['context' => 'dipas_stories']
          )
        );
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigurationArray(FormStateInterface $form_state) {
    return [
      'storyIndexURL' => $this->storyIndexURL,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function injectConfiguration(\stdClass &$pluginSection) {
    $pluginSection->name = $this->t('Story Selector', [], ['context' => 'dipas_stories']);
    $pluginSection->icon = 'bi-bookshelf';
    $pluginSection->renderToWindow = FALSE;
    $pluginSection->storyIndexURL = $this->storyIndexURL;
    $pluginSection->initialWidth = $this->initialWidth;
    $pluginSection->initialWidthMobile = $this->initialWidthMobile;
  }
}
