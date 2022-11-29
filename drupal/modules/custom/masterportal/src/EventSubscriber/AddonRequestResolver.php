<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class AddonRequestResolver
 *
 * Resolves requests to Masterportal addons by delivering libraries from the libraries folder.
 *
 * @package Drupal\dipas\EventSubscriber
 */
class AddonRequestResolver implements EventSubscriberInterface {

  /**
   * The currently processed request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * AddonRequestResolver constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The currently processed request.
   */
  public function __construct(RequestStack $request_stack) {
    $this->currentRequest = $request_stack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::RESPONSE => ['onResponse'],
    ];
  }

  /**
   * Event subscriber handler method.
   */
  public function onResponse(ResponseEvent $event) {
    $requestUriPattern = sprintf(
      '~^%1$s%2$s/libraries/masterportal/(js|css)/~',
      base_path(),
      drupal_get_path('module', 'masterportal')
    );
    if (preg_match($requestUriPattern, $this->currentRequest->getRequestUri(), $matches)) {
      $fileRequested = pathinfo(
        $this->currentRequest->getRequestUri(),
        PATHINFO_BASENAME
      );
      $libraries_path = sprintf(
        '%1$s/libraries/MasterportalAddons/%2$s',
        DRUPAL_ROOT,
        $fileRequested
      );
      if (file_exists($libraries_path)) {
        $event->setResponse(new BinaryFileResponse(
          $libraries_path,
          200,
          [
            'Content-Type'=> $matches[1] == 'js' ? 'application/javascript' : 'text/css'
          ]
        ));
      }
    }
  }

}
