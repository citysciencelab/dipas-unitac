<?php

namespace Drupal\dipas_stories\Service;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Http\RequestStack;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Render\HtmlResponse;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\dipas\Plugin\ResponseKey\DateTimeTrait;
use Drupal\dipas\Plugin\ResponseKey\NodeListingTrait;
use Drupal\dipas_stories\LoadEntityTrait;
use Drupal\dipas_stories\MapSettingsTrait;
use Drupal\dipas_stories\StoryRelationHandlerTrait;
use Drupal\dipas_stories\StoryStepTrait;
use Drupal\masterportal\DomainAwareTrait;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class DipasStoriesApi implements DipasStoriesApiInterface {

  use NodeListingTrait,
    DateTimeTrait,
    DomainAwareTrait,
    LoadEntityTrait,
    StoryStepTrait,
    MapSettingsTrait,
    StoryRelationHandlerTrait;

  /**
   * Options to pass to the json_encode function.
   */
  const JSON_OUTPUT_OPTIONS = JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\dipas_stories\Service\Connection
   */
  protected $database;

  /**
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * @var \Symfony\Component\HttpFoundation\Request|null
   */
  protected $currentRequest;

  /**
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $stepViewBuilder;

  /**
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    Connection $database,
    DateFormatterInterface $date_formatter,
    CacheBackendInterface $cache,
    RequestStack $request_stack,
    RendererInterface $renderer,
    FileUrlGeneratorInterface $file_url_generator,
    ConfigFactoryInterface $config_factory,
    LoggerChannelInterface $logger
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->database = $database;
    $this->dateFormatter = $date_formatter;
    $this->cache = $cache;
    $this->renderer = $renderer;
    $this->fileUrlGenerator = $file_url_generator;
    $this->configFactory = $config_factory;
    $this->logger = $logger;

    $this->currentRequest = $request_stack->getCurrentRequest();
    $this->stepViewBuilder = $this->entityTypeManager->getViewBuilder('story_step');

    $this->listingIsDomainSensitive(TRUE);
  }

  /**
   * Constructs a new JsonResponse and sets encoding options.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  protected function getJsonResponse() {
    $response = new JsonResponse();
    $response->setEncodingOptions(static::JSON_OUTPUT_OPTIONS);

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function requestResolver($storyID, $chapterID) {
    if (is_null($storyID) && is_null($chapterID)) {
      return $this->storyOverview();
    }
    else if (is_null($chapterID)) {
      return $this->storyStructure($storyID);
    }

    return $this->storyStepContents($storyID, $chapterID);
  }

  /**
   * Helper function to retrieve an image URL from a media entity.
   *
   * @param int $mediaID
   *   The ID of the media entity
   *
   * @return string
   *   The URL to the Image.
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function getMediaFileURL($mediaID) {
    /* @var \Drupal\Core\Entity\ContentEntityInterface $titleImage */
    $mediaEntity = $this->getEntity('media', $mediaID);
    $fileEntityID = $mediaEntity->get('field_media_image')->first()->get('target_id')->getString();
    $fileEntity = $this->getEntity('file', $fileEntityID);

    return $this->fileUrlGenerator->generateAbsoluteString($fileEntity->get('uri')->first()->getString());
  }

  /**
   * Helper function to retrieve the image alt text of a image media entity
   *
   * @param int|String $mediaID
   *   The media entity ID
   *
   * @return string
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function getImageMediaAltText($mediaID) {
    /* @var \Drupal\Core\Entity\ContentEntityInterface $mediaEntity */
    $mediaEntity = $this->getEntity('media', $mediaID);

    return $mediaEntity->get('field_media_image')->first()->get('alt')->getString();
  }

  /**
   * Returns an overview of available stories in JSON format.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  protected function storyOverview() {
    $cacheID = sprintf('dipas-stories:%s', $this->getActiveDomain());
    $response = $this->getJsonResponse();

    if (
      !$this->currentRequest->query->has('noCache') &&
      ($cache = $this->cache->get($cacheID))
    ) {
      $responseData = $cache->data;
    }
    else {
      $proceedingConfig = $this->configFactory->get(sprintf(
        'dipas.%s.configuration',
        $this->getActiveDomain()
      ));

      $story_masterportal_baseroute = Url::fromRoute(
        'masterportal.fullscreen',
        ['masterportal_instance' => 'default.dipas_story_telling'],
        ['absolute' => TRUE]
      )->toString();

      $story_structure_baseroute = Url::fromRoute(
        'dipas_stories.stories',
        [],
        ['absolute' => TRUE]
      )->toString();

      $stories = $this->getNodes();

      $responseData = [
        'proceedingname' => $proceedingConfig->get('ProjectInformation.site_name'),
        'proceedingurl' => sprintf(
          'https://%s/%s',
          $this->currentRequest->getHttpHost(),
          $proceedingConfig->get('domain')
        ),
        'storycount' => count($stories),
        'storybaseurl' => sprintf(
          '%s?story=%s',
          $story_masterportal_baseroute,
          urlencode($story_structure_baseroute) . '/'
        ),
        'toolheadline' => 'DIPAS stories',
        'stories' => $stories,
      ];

      $cacheTags = array_merge(
        ['dipas-stories'],
        array_map(
          function ($story) {
            return sprintf('node:%d', $story->nid);
          },
          $stories
        )
      );

      $this->cache->set(
        $cacheID,
        $responseData,
        Cache::PERMANENT,
        $cacheTags
      );
    }

    $response->setData($responseData);

    return $response;
  }

  /**
   * Returns the structural representation of a DIPAS story in JSON.
   *
   * @param int $storyID
   *   The ID of the story node.
   *
   * @returns \Symfony\Component\HttpFoundation\JsonResponse
   */
  protected function storyStructure($storyID) {
    $cacheID = sprintf("dipas-story-%d", $storyID);
    $response = $this->getJsonResponse();

    if (
      !$this->currentRequest->query->has('noCache') &&
      ($cache = $this->cache->get($cacheID))
    ) {
      $response->setData($cache->data);
    }
    else {
      /* @var \Drupal\node\NodeInterface */
      $node = $this->getEntity('node', $storyID);

      /* @var \Drupal\user\UserInterface $author */
      $author = $this->getEntity('user', $node->get('uid')->first()->getString());

      $responseData = [
        'title' => $node->getTitle(),
        'author' => $author->getDisplayName(),
        'description' => $node->get('field_text')->first()->getString(),
        'coverImagePath' => $this->getMediaFileURL($node->get('field_title_image')->first()->getString()),
        'coverImageAlt' => $this->getImageMediaAltText($node->get('field_title_image')->first()->getString()),
        'htmlFolder' => 'chapter',
        'displayType' => $node->get('field_display_type')->first()->getString(),
        'reading_time' => $this->getStoryReadingTime($node),
        'steps' => [],
      ];

      $steps = $this->getStoryStepReferenceFieldData('node', $storyID, 'field_story_steps');

      $stepEntities = $this->getEntities(
        'story_step',
        array_map(
          function ($elem) {
            return $elem->field_story_steps_target_id;
          },
          $steps
        )
      );

      $step_cachetags = array_map(
        function ($elem) {
          return sprintf('story_step:%s', $elem->field_story_steps_target_id);
        },
        $steps
      );

      $referenceContainer = [];

      foreach ($steps as $step) {
        $mapSettings = $this->getMapSettingsFromStoryStep($step->field_story_steps_target_id);
        $titleImageID = ($field = $stepEntities[$step->field_story_steps_target_id]->get('field_title_image')->first()) ? $field->getString() : FALSE;

        $stepData = [
          'title' => $stepEntities[$step->field_story_steps_target_id]->label(),
          'titleImage' => $titleImageID !== FALSE ? $this->getMediaFileURL($titleImageID) : '',
          'titleImageAlt' => $titleImageID !== FALSE ? $this->getImageMediaAltText($titleImageID) : '',
          'htmlFile' => sprintf('%d.html', $step->field_story_steps_target_id),
          'layers' => $mapSettings ? array_values(array_filter(array_unique(array_merge(
            $mapSettings->BackgroundLayer->visibleLayers,
            $mapSettings->ForegroundLayer->visibleLayers
          )))) : [],
          'interactionAddons' => $mapSettings
            ? $mapSettings->Tools->activeToolPlugin !== 'none'
                ? [$mapSettings->Tools->activeToolPlugin]
                : []
            : [],
        ];

        if (isset($mapSettings->MapFeatures) && in_array('threedimensional', $mapSettings->MapFeatures)) {
          $stepData['is3D'] = TRUE;
          $stepData['navigation3D'] = [
            'cameraPosition' => $mapSettings->ViewpointConfiguration->cameraPosition,
            'heading' => floatval($mapSettings->ViewpointConfiguration->cameraHeading),
            'pitch' => floatval($mapSettings->ViewpointConfiguration->cameraPitch),
          ];
        }
        else {
          $stepData['centerCoordinate'] = $mapSettings->ViewpointConfiguration->startCenter ?? NULL;
          $stepData['zoomLevel'] = $mapSettings->ViewpointConfiguration->zoomLevel ?? NULL;
        }

        if (
          $step->field_story_steps_pid === NULL ||
          $step->field_story_steps_pid === '0'
        ) {
          $responseData['steps'][] = $stepData;
          $referenceContainer[$step->field_story_steps_target_id] = &$responseData['steps'][count($responseData['steps']) - 1];
        }
        else {
          if (!isset($referenceContainer[$step->field_story_steps_pid]['steps'])) {
            $referenceContainer[$step->field_story_steps_pid]['steps'] = [];
          }

          $referenceContainer[$step->field_story_steps_pid]['steps'][] = $stepData;
          $referenceContainer[$step->field_story_steps_target_id] = &$referenceContainer[$step->field_story_steps_pid]['steps'][count($referenceContainer[$step->field_story_steps_pid]['steps']) - 1];
        }
      }

      $this->cache->set(
        $cacheID,
        $responseData,
        Cache::PERMANENT,
        array_merge(
          ['dipas-story', sprintf('node:%d', $storyID)],
          $step_cachetags
        )
      );

      $response->setData($responseData);
    }

    return $response;
  }

  /**
   * Returns the ready-to-use content of a story step in HTML.
   *
   * @param int $chapterID
   *   The ID of the requested chapter.
   *
   * @returns \Symfony\Component\HttpFoundation\JsonResponse
   */
  protected function storyStepContents($storyID, $chapterID) {
    $chapterID = explode('.', $chapterID);
    $chapterID = array_shift($chapterID);

    $cacheID = sprintf('dipas-story-%s-chapter-%s', $storyID, $chapterID);
    $response = new HtmlResponse();
    $response->setMaxAge(0);

    if (
      !$this->currentRequest->query->has('noCache') &&
      ($cache = $this->cache->get($cacheID))
    ) {
      $response->setContent($cache->data);
    }
    else {
      $chapter = $this->getEntity('story_step', $chapterID);
      $view = $this->stepViewBuilder->view($chapter, 'dipas_story');
      $view['#cache']['max-age'] = -1;
      $content = $this->renderer->renderRoot($view);

      if (!strlen(trim($content))) {
        $this->logger->warning("Chapter-Content empty! View-Content (JSONed): @view", ['@view' => json_encode($view)]);
      }

      $response->setContent($content);

      $this->cache->set(
        $cacheID,
        $content,
        Cache::PERMANENT,
        [
          'dipas-story',
          sprintf('node:%s', $storyID),
          sprintf('story_step:%s', $chapterID),
        ]
      );
    }

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function getStoryReadingTime(NodeInterface $node) {
    if ($node->isNew()) {
      return 0;
    }

    $reading_time = 0;
    $steps = array_filter(
      array_map(
        function ($item) {
          return $item['target_id'] ?? NULL;
        },
        $node->get('field_story_steps')->getValue()
      )
    );

    $stepEntities = array_filter($this->getEntities('story_step', $steps));

    array_walk(
      $stepEntities,
      function (ContentEntityInterface $step) use (&$reading_time) {
        if(!is_null($step->get('field_reading_time')->first())) {
          $reading_time += (int) $step->get('field_reading_time')->first()->getString();
        } else {
          $reading_time = 1;
        }
      }
    );

    return $reading_time;
  }

  /**
   * {@inheritdoc}
   */
  protected function getNodeType() {
    return 'story';
  }

  /**
   * {@inheritdoc}
   */
  protected function postProcessNodes(array &$nodes) {
    foreach ($nodes as &$node) {
      $fileEntity = $this->getEntity('file', $node->coverImagePath);
      $node->coverImagePath = $this->fileUrlGenerator->generateAbsoluteString($fileEntity->get('uri')->first()->getString());
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getDatabase() {
    return $this->database;
  }

  /**
   * {@inheritdoc}
   */
  protected function getJoins() {
    return [
      [
        'type' => 'LEFT',
        'table' => 'node__field_text',
        'alias' => 'abstract',
        'condition' => 'base.type = abstract.bundle AND base.nid = abstract.entity_id AND base.vid = abstract.revision_id AND attr.langcode = abstract.langcode AND abstract.deleted = 0',
        'fields' => [
          'field_text_value' => 'description',
        ],
      ],
      [
        'type' => 'LEFT',
        'table' => 'node__field_story_steps',
        'alias' => 'steps',
        'condition' => 'base.type = steps.bundle AND base.nid = steps.entity_id AND base.vid = steps.revision_id AND attr.langcode = steps.langcode AND steps.deleted = 0',
      ],
      [
        'type' => 'LEFT',
        'table' => 'story_step__field_reading_time',
        'alias' => 'time',
        'condition' => 'steps.field_story_steps_target_id = time.entity_id',
      ],
      [
        'type' => 'LEFT',
        'table' => 'node__field_title_image',
        'alias' => 'timagemedia',
        'condition' => 'base.type = timagemedia.bundle AND base.nid = timagemedia.entity_id AND base.vid = timagemedia.revision_id AND attr.langcode = timagemedia.langcode AND timagemedia.deleted = 0',
      ],
      [
        'type' => 'LEFT',
        'table' => 'media__field_media_image',
        'alias' => 'timage',
        'condition' => 'timage.entity_id = timagemedia.field_title_image_target_id',
        'fields' => [
          'field_media_image_target_id' => 'coverImagePath',
          'field_media_image_alt' => 'coverImageAlt',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getExpressions() {
    return [
      'SUM(time.field_reading_time_value)' => 'reading_time',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getConditions() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  protected function getGroupBy() {
    return [
      'base.nid',
      'abstract.field_text_value',
      'timage.field_media_image_target_id',
      'timage.field_media_image_alt',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getSortingField() {
    return 'created';
  }

  /**
   * {@inheritdoc}
   */
  protected function getSortingDirection() {
    return 'DESC';
  }

  /**
   * {@inheritdoc}
   */
  protected function getDateFormatter() {
    return $this->dateFormatter;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityTypeManager() {
    return $this->entityTypeManager;
  }

}
