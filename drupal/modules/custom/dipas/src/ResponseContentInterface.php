<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas;

/**
 * Interface ResponseContentInterface
 *
 * @package Drupal\dipas
 */
interface ResponseContentInterface {

  /**
   * Returns the HTTP status code of the response.
   *
   * @return int
   */
  public function getResponseStatusCode();

  /**
   * Returns the response content data.
   *
   * @return array
   */
  public function getResponseContent();

  /**
   * Returns TRUE if the request was not processed successfully.
   *
   * @return bool
   */
  public function isError();

  /**
   * Returns the error message in case of errors.
   *
   * @return string
   */
  public function getErrorMessage();

  /**
   * Returns the response content set by the constructor raw.
   *
   * @return mixed
   */
  public function getRawContent();

  /**
   * Updates the response content.
   *
   * @param mixed $content
   *   The updated content.
   *
   * @return void
   */
  public function updateContent($content);

}
