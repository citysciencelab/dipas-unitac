<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\Masterportal\LayerStyle;

use Drupal\masterportal\DomainAwareTrait;
use Drupal\masterportal\PluginSystem\LayerStylePluginInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Class ContributionStyles
 *
 * @LayerStyle(
 *   id = "contributionstyles",
 *   title = @Translation("GeoJSON layer styles for the DIPAS contribution feed.")
 * )
 *
 * @package Drupal\dipas\Plugin\Masterportal\LayerStyle
 */
class ContributionStyles implements LayerStylePluginInterface {

  use DomainAwareTrait;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $termStorage;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fileStorage;

  /**
   * ContributionStyles constructor.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct() {
    $serviceContainer = \Drupal::getContainer();
    $this->entityTypeManager = $serviceContainer->get('entity_type.manager');
    $this->termStorage = $this->entityTypeManager->getStorage('taxonomy_term');
    $this->fileStorage = $this->entityTypeManager->getStorage('file');
  }

  /**
   * {@inheritdoc}
   */
  public function getStyleObject() {
    $style = (object) [
      'styleId' => 'contributions',
      'rules' => [],
    ];

    foreach ($this->getAllCategoryTerms() as $topic) {
      $topicImageId = $topic->get('field_category_icon')->first()->get('target_id')->getValue();
      $topicImage = $this->fileStorage->load($topicImageId);

      $topicColor = trim(strtolower($topic->get('field_color')->first()->getString()));
      if (substr($topicColor, 0, 1) === '#') {
        $topicColor = substr($topicColor, 1);
      }
      if (strlen($topicColor) === 3) {
        $topicColor = str_repeat($topicColor[0], 2) . str_repeat($topicColor[1], 2) . str_repeat($topicColor[2], 2);
      }
      $topicColor = hexdec($topicColor);
      $topicColorRGB = [
        ($topicColor & 0xFF0000) >> 16,
        ($topicColor & 0x00FF00) >> 8,
        $topicColor & 0x0000FF,
      ];

      $style->rules[] = (object) [
        'conditions' => (object) [
          'properties' => (object) [
            'Thema' => $topic->label(),
          ],
        ],
        'style' => (object) [
          'type' => 'icon',
          'imageName' => preg_replace('~^https?:~', '', file_create_url($topicImage->getFileUri())),
          'imagePath' => '',
          'imageScale' => 0.5,
          'imageOffsetX' => 0.5,
          'imageOffsetY' => 0.5,
          'lineStrokeColor' => array_merge($topicColorRGB, [1]),
          'lineStrokeWidth' => 3,
          'polygonStrokeColor' => array_merge($topicColorRGB, [1]),
          'polygonStrokeWidth' => 3,
          'polygonFillColor' => array_merge($topicColorRGB, [0.3]),
          'clusterType' => 'circle',
          'clusterCircleRadius' => 15,
          'clusterCircleFillColor' => [100, 200, 230, 0.7],
          'clusterCircleStrokeColor' => [255, 255, 255, 1],
          'clusterCircleStrokeWidth' => 2,
          'clusterTextType' => 'counter',
          'clusterTextAlign' => 'center',
          'clusterTextScale' => 2,
          'clusterTextFillColor' => [0, 0, 0],
          'clusterTextOffsetX' => 0,
          'clusterTextOffsetY' => 1,
        ],
      ];
    }

    return $style;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $cacheTags = ['dipas:contributionstyles'];
    $cacheTags = array_merge($cacheTags, array_map(
      function (TermInterface $term) {
        return sprintf('taxonomy_term:%d', $term->id());
      },
      $this->getAllCategoryTerms()
    ));
    return $cacheTags;
  }

  /**
   * Returns all taxonomy terms of tid categories (singleton).
   *
   * @return \Drupal\taxonomy\TermInterface[]
   */
  protected function getAllCategoryTerms() {
    $allTerms = drupal_static('dipas_categories');
    if (is_null($allTerms)) {
      $entityQuery = $this->termStorage->getQuery();
      $entityQuery->condition('vid', 'categories', '=');

      if ($this->isDomainModuleInstalled()) {
        $conditionGroup = $entityQuery->orConditionGroup();
        $conditionGroup->condition('field_domain_access', $this->getActiveDomain(), '=');
        $conditionGroup->condition('field_domain_all_affiliates', TRUE, '=');
        $entityQuery->condition($conditionGroup);
      }

      $termIDs = $entityQuery->execute();
      $allTerms = $this->termStorage->loadMultiple($termIDs);
    }
    return $allTerms;
  }

}
