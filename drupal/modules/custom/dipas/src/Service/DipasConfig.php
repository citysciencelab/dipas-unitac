<?php

namespace Drupal\dipas\Service;

use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\masterportal\DomainAwareTrait;

/**
 * The DipasConfig class proxies the ConfigEntity for the current active domain.
 *
 * @package Drupal\dipas\Service
 */
class DipasConfig implements DipasConfigInterface {

  use DomainAwareTrait;

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   *   The Drupal config factory instance.
   */
  protected $configFactory;

  /**
   * @var \Drupal\Core\Config\ImmutableConfig
   *   The Config of the current active domain or "default".
   */
  protected $configuration;

  /**
   * DipasConfig constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Drupal's configuration factory service.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
    // Layer configuration is special to each instance.
    $this->configuration = $this->configFactory->get(
      sprintf(
        'dipas.%s.configuration',
        $this->getActiveDomain()
      )
    );
  }

  /**
   * {@inheritdoc}
   */
  public function get($key) {
    return $this->configuration->get($key);
  }

  /**
   * {@inheritdoc}
   */
  public function id(): string {
    return $this->configuration->getName();
  }

  /**
   * {@inheritdoc}
   */
  public function getEditable($id = 'dipas.default.domain'): Config {
    return $this->configFactory->getEditable($id);
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    if ($this->isDomainModuleInstalled()) {

      $ids = [];
      /** @var \Drupal\domain\DomainStorage $domainStorage */
      $domainStorage = \Drupal::entityTypeManager()->getStorage('domain');

      $domains = $domainStorage->loadMultiple();

      foreach ($domains as $domain) {
        /** @var \Drupal\domain\Entity\Domain $domain */
        $ids[] = sprintf("dipas.%s.configuration", $domain->id());
      }

      return $ids;
    }

    return [
      'dipas.default.configuration',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigDomain() {
    return $this->getActiveDomain();
  }

}
