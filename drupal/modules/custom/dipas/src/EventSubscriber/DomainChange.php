<?php

namespace Drupal\dipas\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\domain\DomainInterface;
use Drupal\masterportal\Event\DomainCreate;
use Drupal\masterportal\Event\DomainEdit;
use Drupal\masterportal\Event\DomainDelete;
use Drupal\masterportal\Event\MasterportalDomainEventInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Database\Connection;

/**
 * @package Drupal\dipas\EventSubscriber
 */
class DomainChange implements EventSubscriberInterface {

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $masterportalInstanceStorage;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      DomainCreate::EVENT_NAME => ['onDomainCreate'],
      DomainEdit::EVENT_NAME => ['onDomainEdit'],
      DomainDelete::EVENT_NAME => ['onDomainDelete'],
    ];
  }

  /**
   * DomainChange constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Drupal's configuration factory service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Drupal's entity type manager service.
   * @param \Drupal\Core\Database\Connection
   *   database connection
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    EntityTypeManagerInterface $entity_type_manager,
    Connection $db_connection
  ) {
    $this->configFactory = $config_factory;
    $this->masterportalInstanceStorage = $entity_type_manager->getStorage('masterportal_instance');
    $this->database = $db_connection;
  }

  /**
   * Callback function.
   *
   * @param \Drupal\masterportal\Event\MasterportalDomainEventInterface $event
   */
  public function onDomainCreate(MasterportalDomainEventInterface $event) {
    // Clone the DIPAS default configuration.
    $defaultDIPASConfig = $this->configFactory->get('dipas.default.configuration');
    $domainDIPASConfig = $this->getDomainConfiguration($event->getDomain()->id(), 'dipas', 'configuration');
    $domainDIPASConfig->setData($defaultDIPASConfig->getRawData());
    $domainDIPASConfig->set('id', sprintf('%s.configuration', $event->getDomain()->id()));
    $domainDIPASConfig->set('domain', $event->getDomain()->id());
    $domainDIPASConfig->set('ProjectInformation.site_name', $event->getDomain()->label());

    // Clone default Masterportal instances.
    foreach ($this->getMasterportalInstanceConfigurations() as $instanceID => $defaultInstance) {
      $domainMasterportalInstance = $this->getDomainConfiguration(
        $event->getDomain()->id(),
        'masterportal.instance',
        $defaultInstance->get('instance_name')
      );
      $domainMasterportalInstance->setData($defaultInstance->getRawData());
      $domainMasterportalInstance->set('id', sprintf('%s.%s', $event->getDomain()->id(), $defaultInstance->get('instance_name')));
      $domainMasterportalInstance->set('domain', $event->getDomain()->id());

      $domainMasterportalInstance->save();
    }

    // Re-set the map configured instances to the cloned ones.
    foreach (
      [
        'ContributionSettings.masterportal_instances.contributionmap',
        'ContributionSettings.masterportal_instances.singlecontribution.instance',
        'ContributionSettings.masterportal_instances.createcontribution',
        'MenuSettings.mainmenu.schedule.mapinstance',
      ] as $configKey
    ) {
      $defaultMasterportalInstanceId = $defaultDIPASConfig->get($configKey);
      $domainDIPASConfig->set($configKey, str_replace('default.', "{$event->getDomain()->id()}.", $defaultMasterportalInstanceId));
    }

    // Save the DIPAS domain configuration.
    $domainDIPASConfig->save();
  }

  /**
   * Callback function.
   *
   * @param \Drupal\masterportal\Event\MasterportalDomainEventInterface $event
   */
  public function onDomainEdit(MasterportalDomainEventInterface $event) {

    $previousDomainId = $event->getPreviousDomain();
    $newDomainId = $event->getDomain()->id();
    $newDomainLabel = $event->getDomain()->label();

    //////////
    // rename the DIPAS configuration
    //////////
    $oldConfigName = 'dipas.'. $previousDomainId .'.configuration';
    $newConfigName = 'dipas.'. $newDomainId .'.configuration';
    $this->configFactory->rename($oldConfigName, $newConfigName);

    //////////
    // rename the Masterportal configurations
    //////////
    foreach ($this->getMasterportalInstanceConfigurations($previousDomainId) as $instanceID => $previousInstanceID) {
      $oldConfigName = 'masterportal.instance.' . $previousDomainId . '.' . $previousInstanceID->get('instance_name');
      $newConfigName = 'masterportal.instance.' . $newDomainId . '.' . $previousInstanceID->get('instance_name');
      $this->configFactory->rename($oldConfigName, $newConfigName);

      $masterportalConfig = $this->getDomainConfiguration($newDomainId, 'masterportal.instance', $previousInstanceID->get('instance_name'));
      $masterportalConfig->set('domain', $newDomainId);
      $masterportalConfig->set('id', $newDomainId . '.' . $previousInstanceID->get('instance_name'));
      // Save the new masterportal configuration.
      $masterportalConfig->save();

      \Drupal::logger('dipas')->notice('rename ' . $oldConfigName . ' change to ' . $newConfigName);
    }

    //////////
    // rename default-Masterportal configuration seperately since it is excluded from 'getMasterportalInstanceConfigurations'
    //////////
    $oldConfigName = 'masterportal.config.' . $previousDomainId . '.layers';
    $newConfigName = 'masterportal.config.' . $newDomainId . '.layers';
    $this->configFactory->rename($oldConfigName, $newConfigName);

    $masterportalConfig = $this->getDomainConfiguration($newDomainId, 'masterportal.config', 'layers');
    $masterportalConfig->set('domain', $newDomainId);
    // Save the new masterportal configuration.
    $masterportalConfig->save();

    //////////
    // rename masterportal layer configuration which is individual for each domain
    //////////
    $oldConfigName = 'masterportal.instance.' . $previousDomainId . '.default';
    $newConfigName = 'masterportal.instance.' . $newDomainId . '.default';
    $this->configFactory->rename($oldConfigName, $newConfigName);

    $masterportalConfig = $this->getDomainConfiguration($newDomainId, 'masterportal.instance', 'default');
    $masterportalConfig->set('domain', $newDomainId);
    $masterportalConfig->set('id', $newDomainId . '.default');
    // Save the new masterportal configuration.
    $masterportalConfig->save();

    //////////
    // Re-set the configured map instances to the renamed ones in dipas-configuration.
    //////////
    $newDomainDIPASConfig = $this->getDomainConfiguration($newDomainId, 'dipas', 'configuration');
    foreach (
      [
        'ContributionSettings.masterportal_instances.contributionmap',
        'ContributionSettings.masterportal_instances.singlecontribution.instance',
        'ContributionSettings.masterportal_instances.createcontribution',
        'MenuSettings.mainmenu.schedule.mapinstance',
        'id',
      ] as $configKey
    ) {
      $oldMasterportalInstanceId = $newDomainDIPASConfig->get($configKey);
      $newDomainDIPASConfig->set($configKey, str_replace($previousDomainId.'.', "{$newDomainId}.", $oldMasterportalInstanceId));
    }
    $newDomainDIPASConfig->set('domain', $newDomainId);

    // Save the new DIPAS domain configuration.
    $newDomainDIPASConfig->save();

    //////////
    // rename and change some system domain access actions
    //////////
    foreach (
      [
        'system.action.domain_access_add_action.',
        'system.action.domain_access_remove_action.',
        'system.action.domain_access_add_editor_action.',
        'system.action.domain_access_remove_editor_action.',
      ] as $configKey
    ) {
      $oldConfigName = $configKey . $previousDomainId;
      $newConfigName = $configKey . $newDomainId;
      $this->configFactory->rename($oldConfigName, $newConfigName);

      // change settings internally
      $systemActionConfig = $this->configFactory->getEditable($newConfigName);
      $systemActionConfig->set('dependencies.config', ['domain.record.'.$newDomainId]);
      $systemActionConfig->set('id', str_replace('system.action.', '', $configKey) . $newDomainId);
      $systemActionConfig->set('configuration.domain_id', $newDomainId);
      // Replace new Domain label in something like: Add selected content to the NameOfPrevious domain
      // oops: todo: geht das auch bei übersetzten Seiten!?!?
      $newActionLabel = preg_replace('/(.* the )(.*)( domain)/', '$1'.$newDomainLabel.'$3', $systemActionConfig->get('label'));
      $systemActionConfig->set('label', $newActionLabel);

      $systemActionConfig->save();
    }

    //////////
    // Move all existing content from old domain_id to new domain_id
    //////////
    foreach (
      [
        'node__field_domain_access',
        'media__field_domain_access',
        'comment__field_domain_access',
        'taxonomy_term__field_domain_access',
        'user__field_domain_access',
      ] as $accessTable
    ) {
      $query = $this->database->update($accessTable)
        ->fields(['field_domain_access_target_id' => $newDomainId])
        ->condition('field_domain_access_target_id', $previousDomainId, '=');
      $result = $query->execute();
    }

    // ToDo: NLP results will not be transfered to the changed domain

    \Drupal::logger('dipas')->notice('DIPAS- and Masterportal-configuration renamed from '. $previousDomainId . ' to '. $newDomainId . ' and existing content transfered.');
  }

  /**
   * Callback function.
   *
   * @param \Drupal\masterportal\Event\MasterportalDomainEventInterface $event
   */
  public function onDomainDelete(MasterportalDomainEventInterface $event) {
    /*
     * The MasterportalInstances are already dealt with,
     * just delete the dipas config.
     */
    $this->getDomainConfiguration($event->getDomain()->id(), 'dipas', 'configuration')->delete();

    /*
     * check, if it is NOT the default domain which shall be deleted
     * then select all connected content
     * check if it is connected ONLY to this domain which is in deletion
     * if that is the case: delete the content
     * otherwise only delete the domain_access_entry for the domain in deletion and keep the content
     */

     if ($event->getDomain()->id() !== 'default') {
      foreach (
        [
          (object) ['table' => 'node__field_domain_access', 'storage' => 'node',],
          (object) ['table' => 'media__field_domain_access', 'storage' => 'media',],
          (object) ['table' => 'comment__field_domain_access', 'storage' => 'comment',],
          (object) ['table' => 'taxonomy_term__field_domain_access', 'storage' => 'taxonomy_term',],
          (object) ['table' => 'user__field_domain_access', 'storage' => 'user',],
        ] as $accessTable
      ) {
        // request all entities, accessible by deleted domain
        $queryForEntityIds = $this->database->select($accessTable->table, 't')
            ->fields('t', ['entity_id'])
            ->condition('t.field_domain_access_target_id', $event->getDomain()->id(), '=');
        $resultEntityIds = $queryForEntityIds->execute()->fetchCol();

        // if there were any entities accessible by the deleted domain, check if they are accessibly by other domains as well
        if (count($resultEntityIds)) {
          // count number of entries for each entity in the access-correlation-table
          $query = $this->database->select($accessTable->table, 't')
            ->fields('t', ['entity_id']);
            $query->addExpression('count(entity_id)', 'entity_id_count');
            $query->condition('entity_id', $resultEntityIds, 'IN');
            $query->groupBy("t.entity_id");
          $result = $query->execute()->fetchAll();

          $idsToDelete = array_column(array_filter($result, function ($set) { return $set->entity_id_count === "1"; }), 'entity_id');
          $idsToDisconnect = array_column(array_filter($result, function ($set) { return $set->entity_id_count !== "1"; }), 'entity_id');

          // delete items to delete
          if (count($idsToDelete)) {
            $itemsToDelete = \Drupal::entityTypeManager()->getStorage($accessTable->storage)->loadMultiple($idsToDelete);

            // Loop through the entities and delete them by calling the delete method.
            foreach ($itemsToDelete as $item) {
              $item->delete();
            }

            \Drupal::logger('dipas')->notice('Für Storage '. $accessTable->table . ' wurden '. count($idsToDelete) .' Entitäten gelöscht');
          }

          // delete connection to deleted domain for items in multiple use
          if (count($idsToDisconnect)) {
            $query = $this->database->delete($accessTable->table)
              ->condition('field_domain_access_target_id', $event->getDomain()->id(), '=')
              ->condition('entity_id', $idsToDisconnect, 'IN');
            $result = $query->execute();

            \Drupal::logger('dipas')->notice('Für Tabelle '. $accessTable->table . ' wurden '. count($idsToDisconnect) .' Verknüpfungen mit Entitäten gelöscht');
          }
        }
      }
     }
  }

  /**
   * Returns the editable domain configuration for a given domain (singleton).
   *
   * @param string $domainId
   *   The id of the domain to get configurations for, or null, if default is requested
   * @param string $configPrefix
   *   The prefix for the configuration excluding the trailing dot: 'dipas'.
   * @param string $configKey
   *   The configuration key identifier: 'configuration'.
   *
   * @return \Drupal\Core\Config\Config
   *   The editable configuration object.
   */
  protected function getDomainConfiguration(string $domainId, $configPrefix, $configKey) {
    $domainConfig = drupal_static('dipas_domain_config', []);
    if (!isset($domainConfig[$configKey])) {
      // Try to load configuration for this subdomain.
      $domainConfig[$configKey] = $this->configFactory->getEditable(
        sprintf(
          '%s.%s.%s',
          $configPrefix,
          $domainId,
          $configKey
        )
      );
    }
    return $domainConfig[$configKey];
  }


  /**
   * Determines the masterportal instances affected by a change.
   *
   * @param string $domainId
   *   The id of the domain to get configurations for, or null, if default is requested
   * @return \Drupal\Core\Config\ImmutableConfig[]|\Drupal\Core\Config\Config[]
   *   If no domain is given, returns the immutable default instances,
   *   otherwise editable instance configurations are returned.
   */
  protected function getMasterportalInstanceConfigurations(string $domainId = NULL) {
    $instances = drupal_static(sprintf(
      'dipas_masterportal_instances.%s',
      $domainId === NULL ? 'default' : $domainId)
    );
    if (is_null($instances)) {
      $query = $this->masterportalInstanceStorage->getQuery();
      // Always exclude default and domain spanning instances.
      $query->condition(
        'instance_name',
        ['default', 'config', 'dipas_projectarea', 'cockpit_map'],
        'NOT IN'
      );
      $query->condition('domain', 'default', '=');
      $instanceIDs = $query->execute();

      $instances = array_combine(
        $instanceIDs,
        $this->configFactory->loadMultiple(
          array_map(function ($id) {
            return sprintf('masterportal.instance.%s', $id);
          }, $instanceIDs)
        )
      );
    }
    return $instances;
  }

}
