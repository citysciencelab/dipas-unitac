<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Exception;

use Drupal\Component\Plugin\Exception\PluginException;

/**
 * Class UnknownPluginMethodException.
 *
 * Exception that is thrown when a plugin triggers an ajax call
 * that has no handler function.
 *
 * @package Drupal\masterportal
 */
class UnknownPluginMethodException extends PluginException {

  /**
   * Construct an UnknownPluginMethodException exception.
   *
   * For the remaining parameters see \Exception.
   *
   * @param string $plugin_id
   *   The plugin ID that was not found.
   *
   * @see \Exception
   */
  public function __construct($plugin_id, $message = '', $code = 0, \Exception $previous = NULL) {
    if (empty($message)) {
      $message = sprintf("Plugin ID '%s' was not found.", $plugin_id);
    }
    parent::__construct($message, $code, $previous);
  }

}
