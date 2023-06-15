<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Service;

use Drupal\Core\Logger\LoggerChannelInterface;

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
   * @var \Drupal\
   */
  protected $logger;

  /**
   * ServiceManager constructor.
   *
   * @param \Drupal\masterportal\Service\MasterportalTokenServiceInterface $token_service
   *   Custom token service.
   * @param \Drupal\masterportal\Service\MasterportalConfigInterface $config
   *   The configuration service of the Masterportal.
   * @param Drupal\Core\Logger\LoggerChannelInterface $logger
   *   Custom logger channel
   */
  public function __construct(
    MasterportalTokenServiceInterface $token_service,
    MasterportalConfigInterface $config,
    LoggerChannelInterface $logger
  ) {
    $this->logger = $logger;

    if ($serviceDefinitionsFile = $config->get('BasicSettings')['service_definitions']) {
      if ($token_service->containsTokens($serviceDefinitionsFile)) {
        $token_service->setFileSystemTokenReplacement(TRUE);
        $serviceDefinitionsFile = $token_service->replaceTokens($serviceDefinitionsFile);
        $serviceDefinitionsFile = realpath(sprintf('%s/%s', DRUPAL_ROOT, $serviceDefinitionsFile));
      }
      if (preg_match('~^https?://~i', $serviceDefinitionsFile)) {
        try {
          /* @var \GuzzleHttp\ClientInterface $guzzle */
          $guzzle = \Drupal::getContainer()->get('http_client');
          $serviceDefinitionsFile = $guzzle->request('GET', $serviceDefinitionsFile)->getBody()->getContents();
        } catch (\Exception $e) {
          $this->logger->error("Unable to fetch services definitions! Error thrown: %error", ['%error' => $e->getMessage()]);
          $serviceDefinitionsFile = '[]';
        }
      }
      else {
        $serviceDefinitionsFile = file_get_contents($serviceDefinitionsFile);
      }
      $this->services = !empty(($decoded = json_decode($serviceDefinitionsFile))) ? $decoded : [];
    }
    else {
      $this->services = [];
    }
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
