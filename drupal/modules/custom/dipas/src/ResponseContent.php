<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas;

/**
 * Class ResponseContent
 *
 * Custom class to aid in consistent response data handling.
 *
 * @package Drupal\dipas
 */
class ResponseContent implements ResponseContentInterface {

  const RESPONSE_STATUS_SUCCESS = 'success';
  const RESPONSE_STATUS_ERROR = 'error';

  protected $status;

  protected $content;

  protected $code;

  /**
   * ResponseContent constructor.
   *
   * @param string $status
   *   The status string.
   * @param mixed $content
   *   The response content.
   * @param int $code
   *   The HTTP status code.
   */
  public function __construct($status, $content, $code = 200) {
    $this->status = $status;
    $this->content = $content;
    $this->code = $code;
  }

  /**
   * {@inheritdoc}
   */
  public function getResponseStatusCode() {
    return $this->code;
  }

  /**
   * {@inheritdoc}
   */
  public function getResponseContent() {
    return !$this->isError()
      ? ['status' => static::RESPONSE_STATUS_SUCCESS] + $this->content
      : [
          'status' => static::RESPONSE_STATUS_ERROR,
          'message' => $this->getErrorMessage()
        ];
  }

  /**
   * {@inheritdoc}
   */
  public function isError() {
    return $this->status === static::RESPONSE_STATUS_ERROR;
  }

  /**
   * {@inheritdoc}
   */
  public function getErrorMessage() {
    return $this->content;
  }

}
