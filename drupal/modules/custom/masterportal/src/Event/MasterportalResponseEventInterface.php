<?php

namespace Drupal\masterportal\Event;

use Symfony\Component\HttpFoundation\Response;

interface MasterportalResponseEventInterface {

  /**
   * @return \Drupal\masterportal\Entity\MasterportalInstanceInterface|FALSE
   */
  public function getMasterportalInstance();

  /**
   * @param string $name
   * @param string $content
   *
   * @return void
   */
  public function addHeader($name, $content);

  /**
   * @param \Symfony\Component\HttpFoundation\Response $response
   *
   * @return void
   */
  public function modifyResponse(Response &$response);

}
