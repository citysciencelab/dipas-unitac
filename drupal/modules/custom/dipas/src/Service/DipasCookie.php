<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Service;

use stdClass;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;

class DipasCookie implements DipasCookieInterface {

  /**
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * @var array
   */
  protected $cookieData;

  /**
   * DipasCookie constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requests
   */
  public function __construct(RequestStack $requests) {
    $this->currentRequest = $requests->getCurrentRequest();
    $this->cookieData = $this->getCookieData() ?: (object) [];
  }

  /**
   * {@inheritdoc}
   */
  public function getCookie() {
    return new Cookie(
      'dipas',
      json_encode($this->cookieData),
      time() + 60 * 60 * 24 * 365,
      '/',
      null,
      FALSE,

      FALSE,
      FALSE,
      'lax'
    );
  }

  /**
   * {@inheritdoc}
   */
  public function hasCookiesEnabled() {
    return $this->currentRequest->cookies->has('dipas');
  }

  /**
   * {@inheritdoc}
   */
  public function getCookieData() {
    return $this->hasCookiesEnabled()
      ? json_decode($this->currentRequest->cookies->get('dipas'))
      : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function setCookieData(stdClass $data) {
    $this->cookieData = $data;
  }

}
