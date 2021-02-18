<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\masterportal\DomainAwareTrait;
use Drupal\masterportal\Entity\MasterportalInstanceInterface;

/**
 * Class InstanceService.
 *
 * Provides functions to deal with instances in a consistent way.
 *
 * @package Drupal\masterportal\Service
 */
class InstanceService implements InstanceServiceInterface {
  use DomainAwareTrait;
  /**
   * Drupal's entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The storage space for Masterportal instances.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $instanceStorage;

  /**
   * Custom logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Drupal's config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * InstanceService constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Drupal's entity type manager service.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   Custom logger channel.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Drupal's config factorsy service.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *   Thrown if the entity type doesn't exist.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   *   Thrown if the storage handler couldn't be loaded.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    LoggerChannelInterface $logger,
    ConfigFactoryInterface $config_factory
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->instanceStorage = $this->entityTypeManager->getStorage('masterportal_instance');
    $this->logger = $logger;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function getInstanceOptions(array $hideInstances = []) {
    $query = $this->instanceStorage->getQuery();
    $query->condition('domain', $this->getActiveDomain(), '=');
    if (count($hideInstances) > 0) {
      $query->condition('instance_name', $hideInstances, 'NOT IN');
    }
    $ids = $query->execute();

    return array_map(
      function (MasterportalInstanceInterface $instance) {
        return $instance->label();
      },
      $this->instanceStorage->loadMultiple($ids)
    );

  }

  /**
   * {@inheritdoc}
   */
  public function loadInstance($id) {
    if (strpos($id, '.') === FALSE) {
      $id = sprintf("%s.%s", $this->getActiveDomain(), $id);
    }
    return $this->instanceStorage->load($id);
  }

  /**
   * {@inheritdoc}
   */
  public function checkInstancesForRemovedLayers(array $removed_layer_ids) {
    // Load all available instances.
    /* @var \Drupal\masterportal\Entity\MasterportalInstanceInterface[] $instances */
    $instances = $this->instanceStorage->loadMultiple();

    // Check each and every instance.
    foreach ($instances as $instance) {

      // Are there any layers contained in the instance that got removed?
      $orphaned_layer_ids = array_intersect($removed_layer_ids, $instance->getAllLayerIdsInUse());
      if (!empty($orphaned_layer_ids)) {

        // Fetch an editable configuration of this instance.
        $editableConfigInstance = $this->configFactory->getEditable(sprintf(
          'masterportal.instance.%s',
          $instance->id()
        ));

        // Handle foreground and background separately.
        foreach (['ForegroundLayerSection', 'BackgroundLayerSettings'] as $section) {

          // Prepare a container for deltas to remove.
          $deltas_to_remove = [];

          // Determine the configured layers in the current section.
          $layersInSection = $editableConfigInstance->get(sprintf('settings.%s.layer', $section));

          // Handle each configured layer separately.
          foreach ($layersInSection as $delta => $layer) {

            // Composite layers out of layers the same meta id.
            if (preg_match('~^\[.*\]$~', $layer['id'])) {

              // First step is to decode the layer id.
              $single_layers = json_decode($layer['id']);

              // Mext, filter out all layer ids that got removed.
              $single_layers = array_filter(
                $single_layers,
                function ($layer_id) use ($orphaned_layer_ids) {
                  return !in_array($layer_id, $orphaned_layer_ids);
                }
              );

              // Reinitialize the indexes for proper encoding.
              $single_layers = array_values($single_layers);

              // If there are layers left, re-set the composite layer id.
              if (!empty($single_layers)) {
                $editableConfigInstance->set(
                  sprintf('settings.%s.layer.%d.id', $section, $delta),
                  json_encode($single_layers)
                );
              }
              // No layers are left, remove this layer entirely.
              else {
                $deltas_to_remove[] = $delta;
              }

            }
            // Standalone layers.
            elseif (in_array($layer['id'], $orphaned_layer_ids)) {
              $deltas_to_remove[] = $delta;
            }

            // Remove all configured layers that are scheduled for a complete removal.
            $deltas_to_remove = array_reverse($deltas_to_remove);
            $layersInSection = $editableConfigInstance->get(sprintf('settings.%s.layer', $section));
            foreach ($deltas_to_remove as $delta) {
              array_splice($layersInSection, $delta, 1);
            }

            // Set the processed array as the new configuration for that section.
            $editableConfigInstance->set(
              sprintf('settings.%s.layer', $section),
              $layersInSection
            );
          }
        }

        // Save the changes.
        $editableConfigInstance->save();

        // Finally, set a log entry.
        $this->logger->warning(
          'Map layers have been removed from instance %label (id: %id). Removed layer ids: %removed_ids.',
          [
            '%label' => $instance->label(),
            '%id' => $instance->id(),
            '%removed_ids' => implode(', ', $orphaned_layer_ids),
          ]
        );

      }
    }
  }

}
