<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\masterportal\Service\InstanceServiceInterface;
use Drupal\masterportal\Service\MasterportalInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Test: Inline-Masterportal without iFrame
 *
 * @FieldFormatter(
 *   id = "masterportal_inline",
 *   label = @Translation("Masterportal (inline)"),
 *   field_types = {
 *     "geofield"
 *   }
 * )
 */
class InlineMasterportal extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * Custom instance service.
   *
   * @var InstanceServiceInterface
   */
  protected $instanceService;

  /**
   * The Masterportal renderer service.
   *
   * @var MasterportalInterface
   */
  protected $masterportalRenderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration,
      $container->get('masterportal.instanceservice'),
      $container->get('masterportal.renderer')
    );
  }

  public function __construct(
    $plugin_id,
    $plugin_definition,
    array $configuration,
    InstanceServiceInterface $instance_service,
    MasterportalInterface $masterportal_renderer
  ) {
    parent::__construct(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings']
    );
    $this->instanceService = $instance_service;
    $this->masterportalRenderer = $masterportal_renderer;
  }

  /**
   * {@inheritdoc}
   */
  public function view(FieldItemListInterface $items, $langcode = NULL) {
    return $this->viewElements($items, $langcode);
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $libpath = drupal_get_path('module', 'masterportal') . '/libraries/masterpotal';
    $renderArray = [
      '#attached' => [
        'library' => [
          'masterportal/inlineLibrary',
        ],
      ],
      '#type' => 'markup',
      '#markup' => <<<HTML
bla
HTML

    ];
    return $renderArray;
  }

}
