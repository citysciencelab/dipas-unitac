<?php

namespace Drupal\dipas_stories\Plugin\Field\FieldWidget\MasterportalSettingsSection;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\dipas_stories\Annotation\MasterportalSettingsSection;

/**
 * Defines a MasterportalSettingsSection plugin implementation for viwpoint configuration settings.
 *
 * @MasterportalSettingsSection(
 *   id = "ViewpointConfiguration",
 *   title = @Translation("Viewpoint configuration"),
 *   description = @Translation("Configure the initial viewport and zoom level for the DIPAS story Masterportal"),
 *   weight = 3
 * )
 */
class ViewpointConfiguration extends PluginBase {

  /**
   * {@inheritdoc}
   */
  public function formElements($fieldname, $pluginID, FormStateInterface $form_state, $delta, array $pluginValues, array $fieldValue) {
    $inputFieldSize = 60;
    $center = $pluginValues['startCenter'] ?? [565874, 5934140];
    $zoomLevel = $pluginValues['zoomLevel'] ?? 3;
    $cameraPosition = $pluginValues['cameraPosition'] ?? [];
    $cameraHeading = $pluginValues['cameraHeading'] ?? '';
    $cameraPitch = $pluginValues['cameraPitch'] ?? '';

    /* @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $editedEntity = $form_state->getformObject()->getEntity();

    $masterportalQueryParams = [
      'preview' => 'true',
      'noCache' => 'true',
    ];

    $threedimensional = FALSE;

    if ($mapsettings = $this->getMapSettingsFromStory($editedEntity->getEntityTypeId(), $editedEntity->id())) {
      $masterportalQueryParams['BackgroundLayer'] = implode('/', $mapsettings->BackgroundLayer->selectedLayers);
      $masterportalQueryParams['ForegroundLayer'] = implode('/', $mapsettings->ForegroundLayer->selectedLayers);

      if ($editedEntity->getEntityTypeId() === 'story') {
        $masterportalQueryParams['VisibleLayers'] = implode('/', array_merge($mapsettings->BackgroundLayer->visibleLayers, $mapsettings->ForegroundLayer->visibleLayers));
      }
      else {
        $mapSettingsFromStoryStep = $this->getMapSettingsFromStoryStep($editedEntity->id());

        $masterportalQueryParams['VisibleLayers'] = implode(
          '/',
          array_merge(
            (!is_null($mapSettingsFromStoryStep) ? $mapSettingsFromStoryStep : $mapsettings)->BackgroundLayer->visibleLayers,
            (!is_null($mapSettingsFromStoryStep) ? $mapSettingsFromStoryStep : $mapsettings)->ForegroundLayer->visibleLayers
          )
        );

        if (
          isset($mapSettingsFromStoryStep->MapFeatures) &&
          is_array($mapSettingsFromStoryStep->MapFeatures) &&
          in_array('threedimensional', $mapSettingsFromStoryStep->MapFeatures)
        ) {
          $threedimensional = TRUE;
        }
      }

    }
    else {
      $masterportalQueryParams['BackgroundLayer'] = '19969';
    }

    if ($threedimensional) {
      $masterportalQueryParams = array_merge(
        $masterportalQueryParams,
        [
          'Map/MapMode' => '3D',
          'cameraPosition' => json_encode($mapSettingsFromStoryStep->ViewpointConfiguration->cameraPosition),
          'cameraHeading' => $mapSettingsFromStoryStep->ViewpointConfiguration->cameraHeading,
          'cameraPitch' => $mapSettingsFromStoryStep->ViewpointConfiguration->cameraPitch,
        ]
      );
    }
    else {
      $masterportalQueryParams = array_merge(
        $masterportalQueryParams,
        [
          'uiStyle' => 'simple',
          'center' => implode(',', $center),
          'zoomLevel' => $zoomLevel,
        ]
      );
    }

    return [
      'mapWidget' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['MasterportalIframeWrapper', 'aspect_ratio_16_9'],
        ],
        '#attached' => [
          'library' => [
            'masterportal/aspectratios',
            'dipas_stories/viewportplugin',
          ],
          'drupalSettings' => [
            'dipas_stories' => [
              'previewMasterportalSrc' => Url::fromRoute(
                'masterportal.fullscreen',
                ['masterportal_instance' => 'default.dipas_story_telling'],
                [
                  'query' => [
                    'preview' => 'true',
                    'uiStyle' => 'simple',
                    'noCache' => 'true',
                  ],
                  'absolute' => TRUE,
                ]
              )->toString(),
            ],
          ],
        ],

        'map' => [
          '#type' => 'html_tag',
          '#tag' => 'iframe',
          '#attributes' => [
            'src' => Url::fromRoute(
              'masterportal.fullscreen',
              ['masterportal_instance' => 'default.dipas_story_telling'],
              ['query' => $masterportalQueryParams]
            )->toString(),
            'class' => ['masterportal'],
            'width' => '100%',
            'height' => '100%',
            'data-fieldname' => $fieldname,
          ],
        ],
      ],

      // Fields for 2D map configuration
      'startCenter' => [
        '#type' => 'textfield',
        '#title' => $this->t('Center coordinates', [], ['context' => 'Masterportal']),
        '#description' => $this->t(
          'The center coordinates for the map to start with. Must be entered in a valid JSON format as an array of 2 integer values.',
          [],
          ['context' => 'dipas_stories']
        ),
        '#default_value' => json_encode($center),
        '#size' => $inputFieldSize,
        '#attributes' => [
          'readonly' => 'readonly',
          'class' => ['center'],
          'data-fieldname' => $fieldname,
        ],
        '#states' => [
          'visible' => [
            ':input[type="checkbox"][name="field_map_settings[0][MapFeatures][activatedMapFeatures][threedimensional]"]' => ['checked' => FALSE],
          ],
        ],
      ],
      'zoomLevel' => [
        '#type' => 'textfield',
        '#title' => $this->t('Initial zoom level', [], ['context' => 'Masterportal']),
        '#description' => $this->t('The initial zoom level that map gets displayed in.', [], ['context' => 'Masterportal']),
        '#min' => 0,
        '#max' => 9,
        '#step' => 1,
        '#default_value' => $zoomLevel,
        '#size' => $inputFieldSize,
        '#attributes' => [
          'readonly' => 'readonly',
          'class' => ['zoomLevel'],
          'data-fieldname' => $fieldname,
        ],
        '#states' => [
          'visible' => [
            ':input[type="checkbox"][name="field_map_settings[0][MapFeatures][activatedMapFeatures][threedimensional]"]' => ['checked' => FALSE],
          ],
        ],
      ],

      // Fields for 3D map configuration
      'cameraPosition' => [
        '#type' => 'textfield',
        '#title' => $this->t('Camera position', [], ['context' => 'dipas_stories']),
        '#description' => $this->t(
          'The camera position coordinates for the map to start with. Must be entered in a valid JSON format as an array of 3 numeric values.',
          [],
          ['context' => 'dipas_stories']
        ),
        '#default_value' => json_encode($cameraPosition),
        '#size' => $inputFieldSize,
        '#attributes' => [
          'readonly' => 'readonly',
          'class' => ['cameraPosition'],
          'data-fieldname' => $fieldname,
        ],
        '#states' => [
          'visible' => [
            ':input[type="checkbox"][name="field_map_settings[0][MapFeatures][activatedMapFeatures][threedimensional]"]' => ['checked' => TRUE],
          ],
        ],
      ],
      'cameraHeading' => [
        '#type' => 'textfield',
        '#title' => $this->t('Camera heading', [], ['context' => 'dipas_stories']),
        '#description' => $this->t('The heading the camera is pointing to.', [], ['context' => 'dipas_stories']),
        '#min' => 0,
        '#max' => 360,
        '#default_value' => $cameraHeading,
        '#size' => $inputFieldSize,
        '#attributes' => [
          'readonly' => 'readonly',
          'class' => ['cameraHeading'],
          'data-fieldname' => $fieldname,
        ],
        '#states' => [
          'visible' => [
            ':input[type="checkbox"][name="field_map_settings[0][MapFeatures][activatedMapFeatures][threedimensional]"]' => ['checked' => TRUE],
          ],
        ],
      ],
      'cameraPitch' => [
        '#type' => 'textfield',
        '#title' => $this->t('Camera pitch', [], ['context' => 'dipas_stories']),
        '#description' => $this->t('The pitch for the camera viewport.', [], ['context' => 'dipas_stories']),
        '#default_value' => $cameraPitch,
        '#size' => $inputFieldSize,
        '#attributes' => [
          'readonly' => 'readonly',
          'class' => ['cameraPitch'],
          'data-fieldname' => $fieldname,
        ],
        '#states' => [
          'visible' => [
            ':input[type="checkbox"][name="field_map_settings[0][MapFeatures][activatedMapFeatures][threedimensional]"]' => ['checked' => TRUE],
          ],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $rawValues, FormStateInterface $form_state) {
    return [
      'startCenter' => json_decode($rawValues['startCenter']),
      'zoomLevel' => $rawValues['zoomLevel'],
      'cameraPosition' => json_decode($rawValues['cameraPosition']),
      'cameraHeading' => $rawValues['cameraHeading'],
      'cameraPitch' => $rawValues['cameraPitch'],
    ];
  }

}
