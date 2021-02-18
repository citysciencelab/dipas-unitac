<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Service;

/**
 * Class ServiceManager.
 *
 * Small custom service to handle the configured services
 * in a consistent manner.
 *
 * @package Drupal\masterportal\Service
 */
class ServiceManager implements ServiceManagerInterface {

  /**
   * The configuration object regarding the configured services.
   *
   * @var array
   */
  protected $services;

  /**
   * ServiceManager constructor.
   *
   * @param \Drupal\masterportal\Service\MasterportalTokenServiceInterface $token_service
   *   Custom token service.
   * @param \Drupal\masterportal\Service\MasterportalConfigInterface $config
   *   The configuration service of the Masterportal.
   */
  public function __construct(
    MasterportalTokenServiceInterface $token_service,
    MasterportalConfigInterface $config
  ) {
    $serviceDefinitionsFile = $config->get('BasicSettings')['service_definitions'];
    if ($token_service->containsTokens($serviceDefinitionsFile)) {
      $serviceDefinitionsFile = $token_service->replaceTokens($serviceDefinitionsFile);
      $serviceDefinitionsFile = realpath(sprintf('%s/%s', DRUPAL_ROOT, $serviceDefinitionsFile));
    }
    $serviceDefinitionsFile = file_get_contents($serviceDefinitionsFile);
    $this->services = !empty(($decoded = json_decode($serviceDefinitionsFile))) ? $decoded : [];
  }

  /**
   * {@inheritdoc}
   */
  public function getServiceOptions() {
    $options = [];
    foreach ($this->services as $service) {
      $options[$service->id] = sprintf('%s (%s)', $service->name, $service->url);
    }
    return $options;
  }

}
