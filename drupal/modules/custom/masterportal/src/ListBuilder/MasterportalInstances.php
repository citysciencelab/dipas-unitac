<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\ListBuilder;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\masterportal\DomainAwareTrait;
use Drupal\masterportal\Entity\MasterportalInstance;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class MasterportalInstances.
 *
 * Provides an overview page of existing Masterportal instance configurations.
 *
 * @package Drupal\masterportal\ListBuilder
 */
class MasterportalInstances extends ConfigEntityListBuilder {

  use DomainAwareTrait;

  /**
   * The user storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $accountStorage;

  /**
   * The currently processed request.
   *
   * @var \Symfony\Component\HttpFoundation\Request|null
   */
  protected $currentRequest;

  /**
   * The user using this listbuilder.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Drupal's extension handling service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager'),
      $container->get('request_stack'),
      $container->get('current_user'),
      $container->get('module_handler')
    );
  }

  /**
   * MasterportalInstances constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Drupal's entity type manager service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The Symfony request stack object.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The user using this listbuilder.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Drupal's extension handling service.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *   Thrown if the entity type doesn't exist.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   *   Thrown if the storage handler couldn't be loaded.
   */
  public function __construct(
    EntityTypeInterface $entity_type,
    EntityTypeManagerInterface $entity_type_manager,
    RequestStack $request_stack,
    AccountInterface $current_user,
    ModuleHandlerInterface $module_handler
  ) {
    parent::__construct($entity_type, $entity_type_manager->getStorage($entity_type->id()));
    $this->accountStorage = $entity_type_manager->getStorage('user');
    $this->currentRequest = $request_stack->getCurrentRequest();
    $this->currentUser = $current_user;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityIds() {
    $query = $this->getStorage()->getQuery()->sort($this->entityType->getKey('id'));

    // Hide hidden entities if necessary.
    $showHidden = $this->currentRequest->query->has('showHidden');
    if (!$showHidden) {
      $query->condition('hidden', TRUE, '<>');
    }

    // Only display entities that the current user has editing access to.
    if (!$this->currentUser->hasPermission('edit any masterportal instances')) {
      $query->condition('uid', $this->currentUser->id(), '=');
    }

    if ($this->isDomainModuleInstalled() && $activeDomain = $this->getActiveDomain()) {
      $query->condition('domain', $activeDomain, '=');
    }

    // Only add the pager if a limit is specified.
    if ($this->limit) {
      $query->pager($this->limit);
    }

    // Execute the query.
    return $ids = $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();

    // Add the toggle to show hidden items
    // Unfortunately, form API elements do not work here
    // (default value problem).
    $build['showHidden'] = [
      '#type' => 'html_tag',
      '#tag' => 'input',
      '#attributes' => [
        'id' => 'showHidden',
        'type' => 'checkbox',
        'style' => 'float: left; margin-right: 10px;',
        'data-url' => Url::fromRoute('masterportal.settings.instances')->toString(),
      ],
      '#weight' => -99998,
      '#attached' => ['library' => ['masterportal/listbuilder']],
    ];

    $build['showHiddenLabel'] = [
      '#type' => 'html_tag',
      '#tag' => 'label',
      '#attributes' => [
        'for' => 'showHidden',
        'style' => 'display: inline;',
      ],
      '#value' => $this->t('Show hidden entities', [], ['context' => 'Masterportal']),
      '#weight' => -99999,
    ];

    if ($this->currentRequest->query->has('showHidden')) {
      $build['showHidden']['#attributes']['checked'] = 'checked';
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Name', [], ['context' => 'Masterportal']);
    $header['owner'] = $this->t('Owner', [], ['context' => 'Masterportal']);
    $header['layers'] = $this->t('Layers', [], ['context' => 'Masterportal']);
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $settings = $entity->get('settings');
    $foreground_layer = count($settings["ForegroundLayerSection"]["layer"]);
    $background_layer = count($settings["BackgroundLayerSettings"]["layer"]);

    $initiallyVisible = 0;
    foreach (['ForegroundLayerSection', 'BackgroundLayerSettings'] as $section) {
      array_walk($settings[$section]['layer'], function ($layer) use (&$initiallyVisible) {
        if (isset($layer['custom']['visibility']) && $layer['custom']['visibility']) {
          $initiallyVisible++;
        }
      });
    }

    $row['label'] = $entity->label();
    $row['owner'] = $this->accountStorage->load($entity->get('uid'))->label();
    $row['layers'] = sprintf(
      '%s, %s, %s',
      $this->formatPlural($foreground_layer, '1 foreground layer', '@count foreground layers', [], ['context' => 'Masterportal']),
      $this->formatPlural($background_layer, '1 background layer', '@count background layers', [], ['context' => 'Masterportal']),
      $this->t('@count initially visible', ['@count' => $initiallyVisible], ['context' => 'Masterportal'])
    );
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getOperations(EntityInterface $entity) {
    // Get the default operations array.
    $operations = parent::getOperations($entity);

    // Move the 'translate' and 'delete' operations to the bottom.
    $operations['translate']['weight'] = 98;
    $operations['delete']['weight'] = 99;

    // Prevent protected instances from being deleted.
    if (
      (in_array($entity->id(), MasterportalInstance::persistentInstances())) ||
      ((int) $entity->get('uid') !== (int) $this->currentUser->id() && !$this->currentUser->hasPermission('delete any masterportal instance'))
    ) {
      unset($operations['delete']);
    }

    // Prevent the "config" entity to be altered by others
    // than those with the "administer site" privilege.
    if ($entity->id() === 'config' && !$this->currentUser->hasPermission('administer site configuration')) {
      unset($operations['edit']);
    }

    // Inject custom links.
    $links = [
      'testlink' => [
        'weight' => 50,
        'link' => ['text' => 'Test', 'arguments' => []],
        'route' => 'masterportal.settings.testpage',
      ],
      'fullscreen' => [
        'weight' => 52,
        'link' => ['text' => 'Open fullscreen', 'arguments' => []],
        'route' => 'masterportal.fullscreen',
        'attributes' => ['target' => '_blank'],
      ],
      'Download' => [
        'weight' => 55,
        'link' => ['text' => 'Download', 'arguments' => []],
        'route' => 'masterportal.download.instance',
      ],
      'config.js' => [
        'weight' => 60,
        'link' => ['text' => 'Check instance specific %file', 'arguments' => ['%file' => 'config.js']],
        'route' => 'masterportal.javascript',
        'attributes' => ['target' => '_blank'],
      ],
      'config.json' => [
        'weight' => 65,
        'link' => ['text' => 'Check instance specific %file', 'arguments' => ['%file' => 'config.json']],
        'route' => 'masterportal.json',
        'attributes' => ['target' => '_blank'],
      ],
      'layerdefinitions.json' => [
        'weight' => 70,
        'link' => ['text' => 'Check instance specific %file', 'arguments' => ['%file' => 'layerdefinitions.json']],
        'route' => 'masterportal.layerdefinitions.instance',
        'attributes' => ['target' => '_blank'],
      ],
    ];
    foreach ($links as $key => $definition) {
      $operations[$key] = [
        'title' => $this->t($definition['link']['text'], $definition['link']['arguments'], ['context' => 'Masterportal']),
        'url' => URL::fromRoute($definition['route'], ['masterportal_instance' => $entity->id()], !empty($definition['attributes']) ? ['attributes' => $definition['attributes']] : []),
        'weight' => $definition['weight'],
      ];
    }

    // Re-sort the weights.
    $weights = [];
    foreach ($operations as $index => $operation) {
      $weights[$index] = $operation['weight'];
    }
    array_multisort($weights, SORT_NUMERIC, $operations);

    // Return the operations (and filter out those damn "translate" links).
    return array_filter(
      $operations,
      function ($link) {
        return isset($link['title']);
      }
    );
  }

}
