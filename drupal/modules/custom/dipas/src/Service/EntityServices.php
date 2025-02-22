<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Service;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeRepositoryInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\Entity\BaseFieldOverride;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\taxonomy\Entity\Term;

/**
 * Class EntityServices.
 *
 * @package Drupal\dipas\Services
 */
class EntityServices implements EntityServicesInterface {

  /**
   * Drupal's entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Drupal's entity type bundle information service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityBundleInfoService;

  /**
   * Drupal's entity field service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Drupal's entity type repository service.
   *
   * @var \Drupal\Core\Entity\EntityTypeRepositoryInterface
   */
  protected $entityTypeRepository;

  /**
   * Drupal's entity display repository service.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * All available content entity types.
   *
   * @var \Drupal\Core\Entity\ContentEntityTypeInterface[]
   */
  protected $contentEntityTypeDefinitions;

  /**
   * The bundle information on entities.
   *
   * @var array
   */
  protected $entityBundleInformation;

  /**
   * The storage for entity display configurations.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $entityViewDisplayStorage;

  /**
   * An array holding the field informations of the different entity bundles.
   *
   * @var array
   */
  protected $bundleFieldDefinitions;

  /**
   * An array holding entity storage interfaces.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface[]
   */
  protected $entityTypeStorageInterfaces;

  /**
   * An array holding entity type viewbuilders.
   *
   * @var array
   */
  protected $entityTypeViewbuilderClasses;

  /**
   * An array holding the display configurations of entities.
   *
   * @var \Drupal\Core\Entity\Entity\EntityViewDisplay[]
   */
  protected $entityDisplayConfigurations;

  /**
   * Drupal's string translation service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected $stringTranslator;

  /**
   * EntityServices constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Drupal's entity type manager service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_bundle_info_service
   *   Drupal's entity type bundle information service.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   Drupal's entity field service.
   * @param \Drupal\Core\Entity\EntityTypeRepositoryInterface $entity_type_repository
   *   Drupal's entity type repository service.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   Drupal's entity display repository service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translator
   *   Drupal's string translator service.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    EntityTypeBundleInfoInterface $entity_bundle_info_service,
    EntityFieldManagerInterface $entity_field_manager,
    EntityTypeRepositoryInterface $entity_type_repository,
    EntityDisplayRepositoryInterface $entity_display_repository,
    TranslationInterface $string_translator
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityBundleInfoService = $entity_bundle_info_service;
    $this->entityFieldManager = $entity_field_manager;
    $this->entityTypeRepository = $entity_type_repository;
    $this->entityDisplayRepository = $entity_display_repository;
    $this->stringTranslator = $string_translator;
    $this->entityViewDisplayStorage = $entity_type_manager->getStorage('entity_view_display');
    $this->contentEntityTypeDefinitions = drupal_static('contentEntityTypeDefinitions', []);
    $this->entityBundleInformation = drupal_static('entityBundleInformation', []);
    $this->bundleFieldDefinitions = drupal_static('bundleFieldDefinitions', []);
    $this->entityTypeStorageInterfaces = drupal_static('entityTypeStorageInterfaces', []);
    $this->entityTypeViewbuilderClasses = drupal_static('entityTypeViewbuilderClasses', []);
    $this->entityDisplayConfigurations = drupal_static('entityDisplayConfigurations', []);
  }

  /**
   * {@inheritdoc}
   */
  public function getContentEntityTypes() {
    if (empty($this->contentEntityTypeDefinitions)) {
      $entity_type_ids = array_keys($this->entityTypeRepository->getEntityTypeLabels());
      $this->contentEntityTypeDefinitions = array_filter(
        array_combine(
          $entity_type_ids,
          array_map(
            function ($entity_type_id) {
              return $this->entityTypeManager->getDefinition($entity_type_id);
            },
            $entity_type_ids
          )
        ),
        function (EntityTypeInterface $entity_type_definition) {
          return $entity_type_definition instanceof ContentEntityTypeInterface;
        }
      );
    }
    return $this->contentEntityTypeDefinitions;
  }

  /**
   * {@inheritdoc}
   */
  public function getContentEntityTypeDefinition($entity_type_id) {
    return $this->getContentEntityTypes()[$entity_type_id];
  }

  /**
   * {@inheritdoc}
   */
  public function getContentEntityTypeOptions() {
    return array_map(
      function (ContentEntityTypeInterface $content_entity_type_definition) {
        return $content_entity_type_definition->getLabel();
      },
      $this->getContentEntityTypes()
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityTypeBundles($entity_type_id) {
    return !empty($this->entityBundleInformation[$entity_type_id])
      ? $this->entityBundleInformation[$entity_type_id]
      : ($this->entityBundleInformation[$entity_type_id] = $this->entityBundleInfoService->getBundleInfo($entity_type_id));
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityTypeBundleOptions($entity_type_id) {
    return array_map(
      function ($bundle) {
        return $bundle['label'];
      },
      $this->getEntityTypeBundles($entity_type_id)
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityTypeBundleViewmodes($entity_type_id, $bundle) {
    $viewmodes = $this->entityDisplayRepository->getViewModeOptionsByBundle($entity_type_id, $bundle);
    if (empty($viewmodes)) {
      $viewmodes = [
        'default' => $this->stringTranslator->translate('Default'),
      ];
    }
    return $viewmodes;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityTypeBundleFields($entity_type_id, $bundles, array $filter = [], $exclude_base_fields = TRUE, array $exclude_fields = []) {

    // Prepare the bundle argument.
    if (!is_array($bundles)) {
      $bundles = explode(',', $bundles);
    }

    // Fetch the bundle field definitio0ns for each provided bundle.
    foreach ($bundles as $bundle) {
      if (empty($this->bundleFieldDefinitions["{$entity_type_id}:{$bundle}"])) {
        $this->bundleFieldDefinitions["{$entity_type_id}:{$bundle}"] = $this->entityFieldManager->getFieldDefinitions($entity_type_id, $bundle);
      }
    }

    // Build an array of all fields (only common fields
    // when multiple bundles were provided).
    $fieldDefinitions = [];
    foreach ($bundles as $bundle) {
      if (empty($fieldDefinitions)) {
        $fieldDefinitions = $this->bundleFieldDefinitions["{$entity_type_id}:{$bundle}"];
      }
      else {
        $fieldDefinitions = array_intersect_key(
          $fieldDefinitions,
          $this->bundleFieldDefinitions["{$entity_type_id}:{$bundle}"]
        );
      }
    }

    // Exclude base field definitions if desired.
    $fieldDefinitions = $exclude_base_fields ? array_filter(
      $fieldDefinitions,
      function (FieldDefinitionInterface $field_definition) {
        return !($field_definition instanceof BaseFieldDefinition || $field_definition instanceof BaseFieldOverride);
      }
    ) : $fieldDefinitions;

    // Exclude fields listed to ignore.
    $fieldDefinitions = array_filter(
      $fieldDefinitions,
      function (FieldDefinitionInterface $field_definition) use ($exclude_fields) {
        return !in_array($field_definition->getName(), $exclude_fields);
      }
    );

    // Apply filters if desired.
    if (empty($filter)) {
      return $fieldDefinitions;
    }
    else {
      $result = $fieldDefinitions;
      foreach ($filter as $function => $value) {
        $result = array_filter(
          $result,
          function (FieldDefinitionInterface $field_definition) use ($function, $value) {
            if (method_exists($field_definition, $function)) {
              if (is_array($value)) {
                return call_user_func_array([$field_definition, $function], $value['arguments']) == $value['value'];
              }
              else {
                return $field_definition->$function() == $value;
              }
            }
            else {
              return TRUE;
            }
          }
        );
      }
      return $result;
    }

  }

  /**
   * {@inheritdoc}
   */
  public function getEntityDisplayConfiguration($entity_type_id, $bundle, $viewmode) {
    $display_id = sprintf('%s.%s.%s', $entity_type_id, $bundle, $viewmode);
    if (!isset($this->entityDisplayConfigurations[$display_id])) {
      $this->entityDisplayConfigurations[$display_id] = $this->entityViewDisplayStorage->load($display_id);
    }
    return $this->entityDisplayConfigurations[$display_id];
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityTypeBundleFieldsInViewMode($entity_type_id, $bundle, $viewmode, $exclude_base_fields = TRUE, array $exclude_fields = []) {
    if ($display_config = $this->getEntityDisplayConfiguration($entity_type_id, $bundle, $viewmode)) {
      // Exclude base field definitions if desired.
      $fields = $exclude_base_fields
        ? array_filter(
            array_keys($display_config->get('content')),
            function ($fieldname) use ($display_config) {
              $field_definition = $display_config->get('fieldDefinitions')[$fieldname];
              return !($field_definition instanceof BaseFieldDefinition || $field_definition instanceof BaseFieldOverride);
            }
          )
        : array_keys($display_config->get('content'));

      // Exclude fields listed to ignore.
      $fields = array_filter(
        $fields,
        function ($fieldname) use ($exclude_fields) {
          return !in_array($fieldname, $exclude_fields);
        }
      );

      // Enrich the array containing the fieldnames with the corresponsing field definitions.
      $fields = array_combine(
        $fields,
        array_map(
          function ($fieldname) use ($display_config) {
            return $display_config->get('fieldDefinitions')[$fieldname];
          },
          $fields
        )
      );

      // Inject the display configuration of each field.
      $view_mode_config = $display_config->get('content');
      foreach (array_keys($fields) as $field) {
        $fields[$field]->display = [
          'type' => $view_mode_config[$field]['type'],
          'settings' => $view_mode_config[$field]['settings'],
          'label' => !empty($view_mode_config['label']) ? $view_mode_config['label'] : '',
          'third_party_settings' => $view_mode_config[$field]['third_party_settings'],
        ];
      }

      return $fields;
    }

    // No fields are present.
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityTypeBundleFieldOptions($entity_type_id, $bundle, array $filter = [], $exclude_base_fields = TRUE) {
    return array_map(
      function (FieldDefinitionInterface $field_definition) {
        return $field_definition->getLabel();
      },
      $this->getEntityTypeBundleFields($entity_type_id, $bundle, $filter, $exclude_base_fields)
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityKey($entity_type_id, $key) {
    if ($definition = $this->getContentEntityTypes()[$entity_type_id]) {
      if ($definition->hasKey($key)) {
        return $definition->getKey($key);
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function loadEntities($entity_type_id, array $ids) {
    return $this->getEntityStorageInterface($entity_type_id)->loadMultiple($ids);
  }

  /**
   * {@inheritdoc}
   */
  public function getQuery($entity_type_id) {
    return $this->getEntityStorageInterface($entity_type_id)->getQuery();
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityViewbuilder($entity_type_id) {
    if (empty($this->entityTypeViewbuilderClasses[$entity_type_id])) {
      $this->entityTypeViewbuilderClasses[$entity_type_id] = $this->entityTypeManager->getViewBuilder($entity_type_id);
    }
    return $this->entityTypeViewbuilderClasses[$entity_type_id];
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityStorageInterface($entity_type_id) {
    if (empty($this->entityTypeStorageInterfaces[$entity_type_id])) {
      $this->entityTypeStorageInterfaces[$entity_type_id] = $this->entityTypeManager->getStorage($entity_type_id);
    }
    return $this->entityTypeStorageInterfaces[$entity_type_id];
  }

  /**
   * {@inheritdoc}
   */
  public function transformStringsIntoTaxonomyTerms($vocabulary, $strings, $createNew = FALSE) {
    if (!is_array($strings)) {
      $strings = [$strings];
    }
    $storage = $this->getEntityStorageInterface('taxonomy_term');
    $result = [];
    foreach ($strings as $string) {
      $term = $storage->loadByProperties([
        'vid' => $vocabulary,
        'name' => $string,
      ]);
      if (count($term)) {
        $result[] = array_shift($term);
      }
      else if ($createNew) {
        $term = Term::create([
          'vid' => $vocabulary,
          'name' => $string,
        ]);
        $term->save();
        $result[] = $term;
      }
    }
    return $result;
  }

}
