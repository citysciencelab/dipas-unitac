<?php

namespace Drupal\dipas\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\domain\DomainInterface;
use Drupal\masterportal\Event\DomainCreate;
use Drupal\masterportal\Event\DomainDelete;
use Drupal\masterportal\Event\MasterportalDomainEventInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @package Drupal\dipas\EventSubscriber
 */
class DomainChange implements EventSubscriberInterface {

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

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
      DomainDelete::EVENT_NAME => ['onDomainDelete'],
    ];
  }

  /**
   * DomainChange constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Drupal's configuration factory service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->configFactory = $config_factory;
    $this->masterportalInstanceStorage = $entity_type_manager->getStorage('masterportal_instance');
  }

  /**
   * Callback function.
   *
   * @param \Drupal\masterportal\Event\MasterportalDomainEventInterface $event
   */
  public function onDomainCreate(MasterportalDomainEventInterface $event) {
    // Clone the DIPAS default configuration.
    $defaultDIPASConfig = $this->configFactory->get('dipas.default.configuration');
    $domainDIPASConfig = $this->getDomainConfiguration($event->getDomain(), 'dipas', 'configuration');
    $domainDIPASConfig->setData($defaultDIPASConfig->getRawData());
    $domainDIPASConfig->set('id', sprintf('%s.configuration', $event->getDomain()->id()));
    $domainDIPASConfig->set('domain', $event->getDomain()->id());
    $domainDIPASConfig->set('ProjectInformation.site_name', $event->getDomain()->label());

    // Clone default Masterportal instances.
    foreach ($this->getMasterportalInstanceConfigurations() as $instanceID => $defaultInstance) {
      $domainMasterportalInstance = $this->getDomainConfiguration(
        $event->getDomain(),
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
  public function onDomainDelete(MasterportalDomainEventInterface $event) {
    /*
     * The MasterportalInstances are already dealt with,
     * just delete the dipas config.
     */
    $this->getDomainConfiguration($event->getDomain(), 'dipas', 'configuration')->delete();
  }

  /**
   * Returns the editable domain configuration for a given domain (singleton).
   *
   * @param \Drupal\domain\DomainInterface $domain
   *   The domain in question.
   * @param string $configPrefix
   *   The prefix for the configuration excluding the trailing dot: 'dipas'.
   * @param string $configKey
   *   The configuration key identifier: 'configuration'.
   *
   * @return \Drupal\Core\Config\Config
   *   The editable configuration object.
   */
  protected function getDomainConfiguration(DomainInterface $domain, $configPrefix, $configKey) {
    $domainConfig = drupal_static('dipas_domain_config', []);
    if (!isset($domainConfig[$configKey])) {
      // Try to load configuration for this subdomain.
      $domainConfig[$configKey] = $this->configFactory->getEditable(
        sprintf(
          '%s.%s.%s',
          $configPrefix,
          $domain->id(),
          $configKey
        )
      );
    }
    return $domainConfig[$configKey];
  }

  /**
   * Determines the masterportal instances affected by a change.
   *
   * @return \Drupal\Core\Config\ImmutableConfig[]|\Drupal\Core\Config\Config[]
   *   If no domain is given, returns the immutable default instances,
   *   otherwise editable instance configurations are returned.
   */
  protected function getMasterportalInstanceConfigurations(DomainInterface $domain = NULL) {
    $instances = drupal_static(sprintf(
      'dipas_masterportal_instances.%s',
      $domain === NULL ? 'default' : $domain->id())
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
