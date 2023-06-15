<?php

namespace Drupal\dipas\EventSubscriber;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class AnonymousRedirectToLogin
 *
 * Redirects all unauthenticated users to the login page., except for DIPAS
 * REST routes.
 *
 * @package Drupal\dipas\EventSubscriber
 */
class AnonymousRedirectToLogin implements EventSubscriberInterface {
  const DIPAS_ROUTES = [
    'dipas.restapi.endpoint',
    'dipas.pdsapi.endpoint',
    'dipas.cockpitdataapi.endpoint',
    'user.login',
    'user.reset.login',
    'image.style_public',
    'entity.user.canonical',
    'system.cron',
    'system.404',
  ];

  /**
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The currently processed request.
   */
  protected $currentRequest;

  /**
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * AnonymousRedirectToLogin constructor.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current, probably logged-in user.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack object
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Drupal's module handler service
   */
  public function __construct(
    AccountInterface $current_user,
    RequestStack $request_stack,
    ModuleHandlerInterface $module_handler,
    LoggerChannelInterface $logger
  ) {
    $this->currentUser = $current_user;
    $this->currentRequest = $request_stack->getCurrentRequest();
    $this->moduleHandler = $module_handler;
    $this->logger = $logger;
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
    $anonymous_access_routes = array_merge(
      static::DIPAS_ROUTES,
      $this->moduleHandler->invokeAll('open_dipas_routes')
    );

    $static_routes = array_filter(
      $anonymous_access_routes,
      function ($route) {
        return $route[0] !== '~';
      }
    );

    $route_patterns = array_diff(
      $anonymous_access_routes,
      $static_routes
    );
    array_walk(
      $route_patterns,
      function (&$pattern) {
        $pattern = trim($pattern, '~^$');
        $pattern = sprintf('^%s$', $pattern);
      }
    );
    $route_patterns = sprintf('~%s~i', implode('|', $route_patterns));

    if (
      ($route = $this->currentRequest->attributes->get('_route')) &&
      !in_array($route, $static_routes) &&
      !preg_match($route_patterns, $route) &&
      !(
        (int) $this->currentUser->id() === 1 ||
        !empty(array_intersect([
          'siteadmin',
          'project_admin',
        ], $this->currentUser->getRoles()))
      )
    ) {
      $this->logger->info('Redirected anonymous request on route @route to user login', ['@route' => $route]);

      // Reset the destination to prevent drupal from overriding the redirect.
      $this->currentRequest->query->set('destination', NULL);

      $url = new Url('user.login', [], ['absolute' => TRUE]);
      $response = new TrustedRedirectResponse($url->toString(), 301);
      $response->setMaxAge(-1);
      $response->setPrivate();

      $event->setResponse($response);
      $event->stopPropagation();

      return $event;
    }
  }

}
