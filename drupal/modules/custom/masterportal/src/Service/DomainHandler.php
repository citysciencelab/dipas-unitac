<?php

namespace Drupal\masterportal\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\domain\DomainInterface;
use Drupal\masterportal\Event\DomainCreate;
use Drupal\masterportal\Event\DomainDelete;
use Drupal\masterportal\Event\DomainEdit;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * {@inheritDoc}
 *
 * @package Drupal\masterportal\Service
 */
class DomainHandler implements DomainHandlerInterface {

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * @var \Drupal\masterportal\EntityStorage\MasterportalInstance
   */
  protected $masterportalInstanceStorage;

  /**
   * DomainHandler constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Drupal's configuration factory service.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   Drupal's event dispatcher service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Drupal's entity type manager.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    EventDispatcherInterface $event_dispatcher,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->configFactory = $config_factory;
    $this->eventDispatcher = $event_dispatcher;
    $this->masterportalInstanceStorage = $entity_type_manager->getStorage('masterportal_instance');
  }

  /**
   * {@inheritdoc}
   */
  public function onDomainEdit(DomainInterface $domain) {
    $domainConfig = $this->getDomainConfiguration($domain, 'masterportal.config', 'layers');
    // "isNew" will be true, if no configuration for this subdomain exists.
    if ($domainConfig->isNew()) {
      $this->createDefaultConfiguration($domain);
      $event = new DomainCreate($domain);
      $this->eventDispatcher->dispatch(DomainCreate::EVENT_NAME, $event);
    }
    else {
      $event = new DomainEdit($domain);
      $this->eventDispatcher->dispatch(DomainEdit::EVENT_NAME, $event);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function onDomainDelete(DomainInterface $domain) {
    // Delete all MasterportalInstances if its not the default.
    if ($domain->id() !== 'default') {
      $query = $this->masterportalInstanceStorage->getQuery();
      $query->condition('domain', $domain->id(), '=');
      $ids = $query->execute();

      $instances = $this->masterportalInstanceStorage->loadMultiple($ids);

      $this->masterportalInstanceStorage->delete($instances);

      $this->getDomainConfiguration($domain, 'masterportal.config', 'layers')->delete();
    }

    $event = new DomainDelete($domain);
    $this->eventDispatcher->dispatch(DomainDelete::EVENT_NAME, $event);
  }

  /**
   * Creates a default set of configuration entries.
   *
   * @param \Drupal\domain\DomainInterface $domain
   */
  protected function createDefaultConfiguration(DomainInterface $domain) {
    /*
     * The default configs apply direct to the domain by the name 'default'
     * nothing to do here.
     */
    if ($domain->id() === 'default') {
      return;
    }

    // Create default layer configurations.
    $layerConfig = $this->getDomainConfiguration($domain, 'masterportal.config', 'layers');
    $defaultConfig = $this->configFactory->get('masterportal.config.default.layers');
    $layerConfig->setData($defaultConfig->getRawData());
    $layerConfig->set('domain', $domain->id());
    $layerConfig->save();

    // Clone the default Masterportal instance.
    $instanceConfig = $this->getDomainConfiguration($domain, 'masterportal.instance', 'default');
    $defaultConfig = $this->configFactory->get('masterportal.instance.default.default');
    $instanceConfig->setData($defaultConfig->getRawData());
    $instanceConfig->set('id', sprintf('%s.default', $domain->id()));
    $instanceConfig->set('instance_name', 'default');
    $instanceConfig->set('domain', $domain->id());

    $instanceConfig->save();
  }

  /**
   * Returns the editable domain configuration for a given domain (singleton).
   *
   * @param \Drupal\domain\DomainInterface $domain
   *   The domain in question.
   * @param string $configPrefix
   *   The prefix for the configuration excluding the trailing dot: 'masterportal.config'
   * @param string $configKey
   *   The configuration key identifier: 'layers'
   *
   * @return \Drupal\Core\Config\Config
   *   The editable configuration object.
   */
  protected function getDomainConfiguration(DomainInterface $domain, $configPrefix, $configKey) {
    $domainConfig = drupal_static('domain_config', []);
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

}
