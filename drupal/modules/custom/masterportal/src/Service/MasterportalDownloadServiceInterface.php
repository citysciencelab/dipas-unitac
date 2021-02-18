<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Service;

use Drupal\masterportal\Entity\MasterportalInstanceInterface;

/**
 * Interface MasterportalDownloadServiceInterface.
 *
 * @package Drupal\masterportal\Service
 */
interface MasterportalDownloadServiceInterface {

  /**
   * Create a ZIP file containing all files needed for this instance.
   *
   * @param \Drupal\masterportal\Entity\MasterportalInstanceInterface $instance
   *   The instance to create the download for.
   *
   * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
   *   The ZIP file.
   */
  public function createZip(MasterportalInstanceInterface $instance);

}
