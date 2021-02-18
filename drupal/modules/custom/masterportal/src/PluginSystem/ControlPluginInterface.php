<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\PluginSystem;

/**
 * Interface ControlPluginInterface.
 *
 * Describes the API for control plugins.
 *
 * @package Drupal\masterportal\PluginSystem
 */
interface ControlPluginInterface extends PluginInterface {

  /**
   * Injects the JavaScript configuration portion (if any).
   *
   * @param \stdClass $config
   *   The config.js Javascript configuration object.
   */
  public function injectJavascriptConfiguration(\stdClass &$config);

  /**
   * Does this plugin provide configuration settings for the config.json?
   *
   * @return bool
   *   TRUE if yes.
   */
  public static function hasJsonConfiguration();

  /**
   * Does this plugin provide configuration settings for the config.js?
   *
   * @return bool
   *   TRUE if yes.
   */
  public static function hasJavascriptConfiguration();

}
