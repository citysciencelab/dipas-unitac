<?php

namespace Drupal\dipas\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\dipas\Service\DipasNlpServicesInterface;
use Drupal\masterportal\DomainAwareTrait;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class NLPBlockBase extends BlockBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;
  use DomainAwareTrait;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $termStorage;

  /**
   * @var \Drupal\dipas\Service\DipasNlpServicesInterface
   */
  protected $nlpServices;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('dipas.nlp_services')
    );
  }

  /**
   * NLPBlockBase constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\dipas\Service\DipasNlpServicesInterface $nlp_services
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    DipasNlpServicesInterface $nlp_services
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->nlpServices = $nlp_services;
  }

  /**
   * Returns a render array with a select field containing term names as options.
   *
   * @param string $vid
   *   The vocabulary id to list the terms for.
   * @param string $title
   *   The title of the select field.
   *
   * @return array
   */
  protected function getTermFilterOptions($vid, $title, $field) {
    $termStorage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    $query = $termStorage->getQuery();
    $query->condition('vid', $vid, '=');
    if ($this->isDomainModuleInstalled()) {
      $this->makeEntityQueryDomainSensitive($query);
    }
    $tids = $query->execute();
    $terms = $termStorage->loadMultiple($tids);
    $terms = array_map(
      function(TermInterface $term) {
        return $term->label();
      },
      $terms
    );

    return $this->getFilterOptions(
      $title,
      $terms,
      [
        'vid' => $vid,
        'field' => $field,
      ]
    );
  }

  /**
   * Returns a render array with a select containing cluster names.
   *
   * @return array
   */
  protected function getClusterFilterOptions() {
    return $this->getFilterOptions(
      'Cluster',
      is_array($this->nlpServices->getClusterOptions()) ? $this->nlpServices->getClusterOptions() : [],
      [
        'field' => 'cluster',
      ]
    );
  }

  /**
   * Returns a select field render array.
   *
   * @param string $title
   *   The title of the field.
   * @param array $options
   *   Options for the field.
   * @param array $dataattributes
   *   Options that should get rendered as "data"-attributes to the field.
   *
   * @return array
   */
  protected function getFilterOptions($title, array $options, array $dataattributes = []) {
    $renderarray = [
      '#type' => 'select',
      '#title' => $this->t($title),
      '#options' => ['' => $this->t('Please choose')] + $options,
      '#attributes' => [
        'class' => ['onChangeFilter'],
      ],
    ];

    foreach ($dataattributes as $key => $value) {
      $renderarray['#attributes']["data-{$key}"] = $value;
    }

    return $renderarray;
  }

}
