<?php

namespace Drupal\masterportal\Event;

use Drupal\masterportal\Entity\MasterportalInstanceInterface;
use stdClass;
use Symfony\Contracts\EventDispatcher\Event;

abstract class MasterportalConfigEventBase extends Event implements MasterportalConfigEventInterface {

  /**
   * @var \Drupal\masterportal\Entity\MasterportalInstanceInterface
   */
  protected $masterportalinstance;

  /**
   * @var stdClass
   */
  protected $configurationObject;

  /**
   * @var array
   */
  protected $cacheTags = [];

  public function __construct(
    stdClass|array $configuration,
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
  public function setConfiguration(stdClass|array $configuration) {
    $this->configurationObject = $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setCacheTags(array $cacheTags) {
    $this->cacheTags = array_values(array_unique(array_merge($this->cacheTags, $cacheTags)));
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return $this->cacheTags;
  }

}
