<?php

namespace Drupal\dipas\EventSubscriber;

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

  /**
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The currently processed request.
   */
  protected $currentRequest;

  /**
   * AnonymousRedirectToLogin constructor.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current, probably logged-in user.
   */
  public function __construct(AccountInterface $current_user, RequestStack $request_stack) {
    $this->currentUser = $current_user;
    $this->currentRequest = $request_stack->getCurrentRequest();
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
    if (
      ($route = $this->currentRequest->attributes->get('_route')) &&
      !(
        in_array($route, [
          'dipas.restapi.endpoint',
          'dipas.pdsapi.endpoint',
          'dipas.cockpitdataapi.endpoint',
          'user.login',
          'image.style_public',
          'entity.user.canonical',
        ]) ||
        preg_match('~^masterportal\.~', $route)
      ) &&
      !(
        (int) $this->currentUser->id() === 1 ||
        !empty(array_intersect([
          'siteadmin',
          'project_admin',
        ], $this->currentUser->getRoles()))
      )
    ) {
      // Reset the destination to prevent drupal from overriding the reidrect.
      $this->currentRequest->query->set('destination', NULL);

      $url = new Url('user.login', [], ['absolute' => TRUE]);
      $response = new TrustedRedirectResponse($url->toString(), 301);
      $event->setResponse($response);
      $event->stopPropagation();
      return $event;
    }
  }

}
