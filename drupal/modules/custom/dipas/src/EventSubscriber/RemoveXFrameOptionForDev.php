<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class RemoveXFrameOptionForDev
 *
 * Removes the HTTP header "X-Frame-Options" for the local DEV environment.
 *
 * @package Drupal\dipas\EventSubscriber
 */
class RemoveXFrameOptionForDev implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::RESPONSE => ['onResponse']
    ];
  }

  /**
   * Event subscriber handler method.
   */
  public function onResponse(ResponseEvent $event) {
    global $config;

    if (isset($config['x_frame_options']) && $config['x_frame_options'] === FALSE) {
      $response = $event->getResponse();
      $response->headers->remove('X-Frame-Options');
    }
  }

}
