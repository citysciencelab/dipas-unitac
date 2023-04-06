<?php

namespace Drupal\masterportal\Event;

use Drupal\masterportal\Entity\MasterportalInstanceInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

abstract class MasterportalResponseEventBase extends Event implements MasterportalResponseEventInterface {

  /**
   * @var array
   */
  protected $header = [];

  /**
   * @var \Drupal\masterportal\Entity\MasterportalInstanceInterface|null
   */
  protected $masterportalInstance;

  public function __construct(MasterportalInstanceInterface $masterportal_instance = NULL) {
    $this->masterportalInstance = $masterportal_instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getMasterportalInstance() {
    if ($this->masterportalInstance) {
      return $this->masterportalInstance;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function addHeader($name, $content) {
    $this->header[$name] = $content;
  }

  /**
   * {@inheritdoc}
   */
  public function modifyResponse(Response &$response) {
    foreach ($this->header as $name => $content) {
      $response->headers->set($name, $content);
    }
  }

}
