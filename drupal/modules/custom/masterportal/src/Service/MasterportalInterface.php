<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Service;

use Drupal\masterportal\Entity\MasterportalInstanceInterface;

/**
 * Interface MasterportalInterface.
 *
 * Defines the API for the integration of the Masterportal into Drupal.
 *
 * @package Drupal\masterportal\Service
 */
interface MasterportalInterface {

  /**
   * Returns a render array that integrates the Masterportal into an iFrame.
   *
   * @param \Drupal\masterportal\Entity\MasterportalInstanceInterface $masterportal_instance
   *   The instance configuration entity of the requested instance.
   * @param string $width
   *   Width of the iFrame element. Set to 100% by default (see routing).
   * @param string $aspectratio
   *   The aspect ratio of the Masterportal. Set to 16:9 by default (see routing).
   * @param int $zoomLevel
   *   The zoomLevel to set (optional).
   * @param string $center
   *   The map center coordinate in LatLng (optional).
   * @param string $marker
   *   The map marker coordinate in LatLng (optional).
   * @param array $query
   *   The options to pass to the Masterportal feeds.
   *
   * @return array
   *   The render array for an iFrame integration.
   */
  public function iframe(
    MasterportalInstanceInterface $masterportal_instance,
    $width,
    $aspectratio,
    $zoomLevel = NULL,
    $center = NULL,
    $marker = NULL,
    array $query = []
  );

  /**
   * Callback function to create and return a response with the desired content.
   *
   * @param string $type
   *   The desired type of response.
   * @param string $content_type
   *   The contents of the "Content-Type" header to send.
   * @param string $preprocess
   *   Potential preprocess functions to act on the response data.
   * @param MasterportalInstanceInterface $masterportal_instance
   *   The instance configuration entity of the requested instance.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   *   In case the requested resource has not been configured, a 404 is thrown.
   *
   * @throws \Drupal\Core\Config\ConfigValueException
   *   In case the content data is not of type string after processing.
   */
  public function createResponse($type, $content_type, $preprocess = NULL, MasterportalInstanceInterface $masterportal_instance = NULL);

}
