<?php

namespace Drupal\dipas_statistics\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\dipas_statistics\Form\Settings;
use Drupal\masterportal\DomainAwareTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class LogAccess
 *
 * Inserts a data row into the database on access to configured DIPAS endpoints.
 *
 * @package Drupal\dipas_statistics\EventSubscriber
 */
class LogAccess implements EventSubscriberInterface {

  use DomainAwareTrait;

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * @var \Symfony\Component\HttpFoundation\Request|null
   */
  protected $currentRequest;

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * LogAccess constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   */
  public function __construct(
    Connection $database,
    RequestStack $request_stack,
    ConfigFactoryInterface $config_factory
  ) {
    $this->database = $database;
    $this->currentRequest = $request_stack->getCurrentRequest();
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::REQUEST => ['onRequest']
    ];
  }

  /**
   * Event subscriber handler method.
   */
  public function onRequest(RequestEvent $event) {
    $config = $this->configFactory->get(Settings::SETTINGS_NAME);

    $routeMapping = [
      'dipas.restapi.endpoint' => [
        'configSection' => 'DIPAS',
        'routePluginParamName' => 'key'
      ],
      'dipas.pdsapi.endpoint' => [
        'configSection' => 'PDS',
        'routePluginParamName' => 'type'
      ],
      'dipas.cockpitdataapi.endpoint' => [
        'configSection' => 'NAVIGATOR',
        'routePluginParamName' => 'data'
      ],
    ];

    $route = $event->getRequest()->attributes->get('_route');

    $arguments = array_filter(
      $event->getRequest()->attributes->all(),
      function ($key) {
        return !preg_match('~^_~', $key);
      },
      ARRAY_FILTER_USE_KEY
    );


    if (
      isset($routeMapping[$route]) &&
      ($configSection = $config->get($routeMapping[$route]['configSection'])) &&
      !empty($configSection) &&
      isset($arguments[$routeMapping[$route]['routePluginParamName']]) &&
      in_array(
        $arguments[$routeMapping[$route]['routePluginParamName']],
        $configSection
      )
    ) {
      $argumentsToLog = $arguments;
      unset($argumentsToLog[$routeMapping[$route]['routePluginParamName']]);

      $logData = [
        'request_time' => time(),
        'proceeding' => $this->getActiveDomain(),
        'api' => $routeMapping[$route]['configSection'],
        'endpoint' => $arguments[$routeMapping[$route]['routePluginParamName']],
        'arguments' => json_encode($argumentsToLog),
      ];

      $this->database
        ->insert('dipas_statistics')
        ->fields($logData)
        ->execute();
    }
  }

}
