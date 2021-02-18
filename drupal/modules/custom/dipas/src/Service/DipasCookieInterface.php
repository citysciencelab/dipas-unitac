<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Service;

use stdClass;

interface DipasCookieInterface {

  /**
   * Returns a cookie object.
   *
   * @return \Symfony\Component\HttpFoundation\Cookie
   */
  public function getCookie();

  /**
   * Returns TRUE if cookies has been enabled.
   *
   * @return bool
   */
  public function hasCookiesEnabled();

  /**
   * Returns the data of the Diüpas cookie or FALSE, if no cookie is present.
   *
   * @return array|FALSE
   */
  public function getCookieData();

  /**
   * Set the cookie data.
   *
   * @param array $data
   */
  public function setCookieData(stdClass $data);

}
