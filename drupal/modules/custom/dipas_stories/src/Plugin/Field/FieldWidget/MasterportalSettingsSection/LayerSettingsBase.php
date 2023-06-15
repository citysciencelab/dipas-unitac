<?php

namespace Drupal\dipas_stories\Plugin\Field\FieldWidget\MasterportalSettingsSection;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TempStore\PrivateTempStore;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class LayerSettingsBase extends PluginBase {

  /**
   * @var \Drupal\Core\Extension\ExtensionPathResolver
   */
  protected $extensionPathResolver;

  /**
   * @var \Drupal\masterportal\Service\LayerServiceInterface
   */
  protected $layerService;

  /**
   * A session-related temp store object.
   *
   * @var PrivateTempStore
   */
  protected $tempStore;

  /**
   * Array of installed gfiThemes.
   *
   * @var array
   */
  protected $gfiThemes;

  /**
   * {@inheritdoc}
   */
  protected function setAdditionalDependencies(ContainerInterface $container) {
    $this->extensionPathResolver = $container->get('extension.path.resolver');
    $this->layerService = $container->get('masterportal.layerservice');
    $this->tempStore = $container->get('tempstore.private')->get('masterportal');

    if (empty($this->gfiThemes = $this->tempStore->get('gfiThemes'))) {
      $this->gfiThemes = [];
      $masterportal = file_get_contents(sprintf(
        '%s/libraries/masterportal/js/masterportal.js',
        $this->extensionPathResolver->getPath('module', 'masterportal')
      ));
      preg_match_all('~("|\')([^"\']+?)\1===[a-z]\.gfiTheme~ism', $masterportal, $matches);
      $this->gfiThemes = array_merge(['default'], $matches[2]);
      $this->tempStore->set('gfiThemes', $this->gfiThemes);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function formElements($fieldname, $pluginID, FormStateInterface $form_state, $delta, array $pluginValues, array $fieldValue) {
    if ($this->widgetMode === 'story_step') {
      /* @var \Drupal\Core\Entity\ContentEntityInterface $entity */
      $editedEntity = $form_state->getformObject()->getEntity();

      $mapSettings = $this->getMapSettingsFromStory($editedEntity->getEntityTypeId(), $editedEntity->id());

      $selectedLayers = $mapSettings ? $mapSettings->{$this->getLayerPropertyName() . 'Layer'}->selectedLayers : [];
      $layerProperties = $mapSettings ? json_encode($mapSettings->{$this->getLayerPropertyName() . 'Layer'}->layerProperties) : '';

      if (isset($pluginValues['visibleLayers'])) {
        $visibleLayers = $pluginValues['visibleLayers'];
      }
      else {
        $visibleLayers = $mapSettings ? $mapSettings->{$this->getLayerPropertyName() . 'Layer'}->visibleLayers : [];
      }
    }
    else {
      $selectedLayers = $pluginValues['selectedLayers'] ?? [];
      $layerProperties = $pluginValues['layerProperties'] ?? "";
      $visibleLayers = $pluginValues['visibleLayers'] ?? [];
    }


    return [
      $this->getLayerPropertyName() => [
        '#type' => 'container',
        '#tree' => TRUE,
        '#attributes' => [
          'data-fieldname' => $fieldname,
          'data-property' => $this->getLayerPropertyName(),
          'class' => ['layerConfiguration'],
        ],
        '#attached' => [
          'library' => ['dipas_stories/layerplugin'],
        ],

        'selectedLayers' => [
          '#type' => 'hidden',
          '#default_value' => implode('/', (array) $selectedLayers),
          '#attributes' => [
            'data-fieldname' => $fieldname,
            'data-property' => $this->getLayerPropertyName(),
            'class' => ['selectedLayers'],
          ],
        ],

        'layerProperties' => [
          '#type' => 'hidden',
          '#default_value' => $layerProperties,
          '#attributes' => [
            'data-fieldname' => $fieldname,
            'data-property' => $this->getLayerPropertyName(),
            'class' => ['layerProperties'],
          ],
        ],

        'visibleLayers' => [
          '#type' => 'hidden',
          '#default_value' => implode('/', $visibleLayers),
          '#attributes' => [
            'data-fieldname' => $fieldname,
            'data-property' => $this->getLayerPropertyName(),
            'class' => ['visibleLayers'],
          ],
        ],

        'layerContainer' => [
          '#type' => 'html_tag',
          '#tag' => 'div',
          '#value' => '<div class="widget" />',
          '#attributes' => [
            'data-fieldname' => $fieldname,
            'data-property' => $this->getLayerPropertyName(),
            'class' => ['layerContainer'],
          ],
        ],

        'layerSelection' => [
          '#type' => 'select2',
          '#title' => $this->t('Select Layer to add', [], ['context' => 'dipas_stories']),
          '#options' => $this->layerService->getLayerOptions(),
          '#select2' => ['allowClear' => TRUE],
          '#default_value' => '',
          '#attributes' => [
            'data-fieldname' => $fieldname,
            'data-property' => $this->getLayerPropertyName(),
            'class' => ['layerSelection'],
          ],
        ],

        'addLayer' => [
          '#type' => 'button',
          '#value' => $this->t('Add layer', [], ['context' => 'dipas_stories']),
          '#executes_submit_callback' => FALSE,
          '#attributes' => [
            'data-fieldname' => $fieldname,
            'data-property' => $this->getLayerPropertyName(),
            'class' => ['layerAdd'],
          ],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $rawValues, FormStateInterface $form_state) {
    $selectedLayers = explode('/', $rawValues[$this->getLayerPropertyName()]['selectedLayers']);
    array_walk($selectedLayers, function (&$id) { $id = trim($id); });

    $visibleLayers = explode('/', $rawValues[$this->getLayerPropertyName()]['visibleLayers']);
    array_walk($visibleLayers, function (&$id) { $id = trim($id); });

    if ($this->widgetMode === 'story') {
      return [
        'selectedLayers' => array_filter($selectedLayers),
        'layerProperties' => $rawValues[$this->getLayerPropertyName()]['layerProperties'],
        'visibleLayers' => array_filter($visibleLayers),
      ];
    }
    else {
      /* @var \Drupal\Core\Entity\ContentEntityInterface $entity */
      $editedEntity = $form_state->getformObject()->getEntity();

      $mapsettings = $this->getMapSettingsFromStory($editedEntity->getEntityTypeId(), $editedEntity->id());
      $previouslyStoredLayerIDs = $mapsettings ? $mapsettings->{$this->getLayerPropertyName() . 'Layer'}->selectedLayers : [];

      $addedLayers = array_filter(
        $selectedLayers,
        function ($id) use ($previouslyStoredLayerIDs) {
          return !in_array($id, $previouslyStoredLayerIDs);
        }
      );

      if ($mapsettings && count($addedLayers)) {
        $mapsettings->{$this->getLayerPropertyName() . 'Layer'}->selectedLayers = array_merge(
          $mapsettings->{$this->getLayerPropertyName() . 'Layer'}->selectedLayers,
          $addedLayers
        );

        $mapsettings->{$this->getLayerPropertyName() . 'Layer'}->layerProperties = $rawValues[$this->getLayerPropertyName()]['layerProperties'];

        /* @var \Drupal\node\NodeInterface $story */
        $story = $this->entityTypeManager
          ->getStorage('node')
          ->load($this->getAssociatedStoryNodeIDForStoryStep($editedEntity->id()));

        $story->set('field_map_settings', json_encode($mapsettings));

        $story->save();
      }

      return [
        'visibleLayers' => $visibleLayers,
      ];
    }
  }

  /**
   * Return the name of the property the layer settings should get stored in.
   *
   * @return string
   */
  abstract protected function getLayerPropertyName();

}
