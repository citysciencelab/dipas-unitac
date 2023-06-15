<?php

namespace Drupal\masterportal\Event;

use Drupal\masterportal\Entity\MasterportalInstanceInterface;
use stdClass;
use Symfony\Contracts\EventDispatcher\Event;

abstract class MasterportalCacheEventBase extends Event implements MasterportalCacheEventsInterface {

  /**
   * @var \Drupal\masterportal\Entity\MasterportalInstanceInterface
   */
  protected $masterportalinstance;

  /**
   * @var string|array
   */
  protected $configurationObject;

  public function __construct(
    String|array $configuration,
    MasterportalInstanceInterface $masterportal_instance = NULL
  ) {
    $this->configurationObject = $configuration;
    $this->masterportalinstance = $masterportal_instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getMasterportalInstance() {
    return $this->masterportalinstance ?? new MasterportalStub();
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configurationObject;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(String|array $configuration) {
    $this->configurationObject = $configuration;
  }

}
