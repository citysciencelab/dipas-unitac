<?php

namespace Drupal\dipas_stories\Plugin\Field\FieldWidget\MasterportalSettingsSection;

use Drupal\Core\Form\FormStateInterface;
use Drupal\dipas_stories\Annotation\MasterportalSettingsSection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a MasterportalSettingsSection plugin implementation for map feature plugins.
 *
 * @MasterportalSettingsSection(
 *   id = "MapFeatures",
 *   title = @Translation("Map Features"),
 *   description = @Translation("Choose and configure map features for the DIPAS story Masterportal"),
 *   weight = 5
 * )
 */
class MapFeatures extends PluginBase {

  /**
   * @var \Drupal\masterportal\PluginSystem\PluginManagerInterface
   */
  protected $mapFeaturesPluginManager;

  /**
   * @var array
   */
  protected array $mapFeatureOptions = [];

  /**
   * {@inheritdoc}
   */
  protected function setAdditionalDependencies(ContainerInterface $container) {
    $this->mapFeaturesPluginManager = $container->get('plugin.manager.dipas_stories.masterportal_map_features');

    foreach ($this->mapFeaturesPluginManager->getPluginDefinitions() as $definition) {
      $this->mapFeatureOptions[$definition['id']] = $definition['title']->__toString();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function formElements($fieldname, $pluginID, FormStateInterface $form_state, $delta, array $pluginValues, array $fieldValue) {
    $element = [
      '#attached' => [
        'library' => ['dipas_stories/mapfeatures'],
      ],
    ];

    if ($this->widgetMode === 'story') {
      $element['selectedMapFeatures'] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('Enabled map features', [], ['context' => 'dipas_stories']),
        '#description' => $this->t('Select the Masterportal features you want to make available for activation in the story steps of this story', [], ['context' => 'dipas_stories']),
        '#options' => $this->mapFeatureOptions,
        '#default_value' => $pluginValues ?? [],
        '#attributes' => [
          'data-fieldname' => $fieldname,
        ],
      ];
    }
    else {
      /* @var \Drupal\Core\Entity\ContentEntityInterface $entity */
      $editedEntity = $form_state->getformObject()->getEntity();

      $mapsettings = $this->getMapSettingsFromStory($editedEntity->getEntityTypeId(), $editedEntity->id());
      $enabledMapFeatures = $mapsettings && isset($mapsettings->MapFeatures) ? (array) $mapsettings->MapFeatures : [];

      $librariesToAttach = [];
      foreach ($enabledMapFeatures as $feature) {
        $pluginDefinition = $this->mapFeaturesPluginManager->getPluginDefinitions($feature);

        if (count($pluginDefinition['libraries'])) {
          $librariesToAttach = array_merge($librariesToAttach, $pluginDefinition['libraries']);
        }
      }
      array_walk($librariesToAttach, function (&$library) { $library = sprintf('dipas_stories/mapfeatures/%s', $library); });

      $element['activatedMapFeatures'] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('Activated map features', [], ['context' => 'dipas_stories']),
        '#description' => $this->t('Select the Masterportal features you want to activate when viewing this story step', [], ['context' => 'dipas_stories']),
        '#options' => array_filter(
          $this->mapFeatureOptions,
          function ($pluginID) use ($enabledMapFeatures) {
            return in_array($pluginID, $enabledMapFeatures);
          },
          ARRAY_FILTER_USE_KEY
        ),
        '#default_value' => $pluginValues ?? [],
        '#attributes' => [
          'class' => ['mapFeature'],
          'data-fieldname' => $fieldname,
        ],
        '#attached' => [
          'library' => $librariesToAttach,
        ],
      ];
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $rawValues, FormStateInterface $form_state) {
    return array_values(array_filter($rawValues[$this->widgetMode === 'story' ? 'selectedMapFeatures' : 'activatedMapFeatures']));
  }

}
