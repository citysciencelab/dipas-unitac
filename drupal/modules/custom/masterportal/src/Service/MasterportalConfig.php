<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\masterportal\DomainAwareTrait;

/**
 * Class MasterportalConfig
 *
 * @package Drupal\masterportal\Service
 */
class MasterportalConfig implements MasterportalConfigInterface {
  use DomainAwareTrait;

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;


  /**
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $basicConfiguration;

  /**
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $layerConfiguration;

  /**
   * MasterportalConfig constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Drupal's configuration factory service.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;

    // Basic config is common accross all instances.
    $this->basicConfiguration = $this->configFactory->get('masterportal.config.basic');

    // The layers are defined per domain
    $activeDomain = $this->getActiveDomain();
    $this->layerConfiguration = $this->configFactory->get('masterportal.config.' . $activeDomain . '.layers');

  }

  /**
   * {@inheritdoc}
   */
  public function get($key) {
    switch ($key) {
      case 'Layerconfiguration':
      case 'LayerStyles':
        return $this->layerConfiguration->get($key);
      default:
        return $this->basicConfiguration->get($key);
    }
  }

}
