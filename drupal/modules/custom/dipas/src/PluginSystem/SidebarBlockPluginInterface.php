<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\PluginSystem;

/**
 * Interface SidebarBlockPluginInterface.
 *
 * Defined the API for sidebar block plugins.
 *
 * @package Drupal\dipas\PluginSystem
 */
interface SidebarBlockPluginInterface {

  /**
   * Returns the plugin's default settings.
   *
   * @return array
   */
  public static function getDefaultSettings();

  /**
   * Returns the plugin's settings form portion.
   *
   * @param string $requiredSelector
   *   jQuery selector that indicates if the plugin is enabled (type: checkbox).
   *
   * @return array
   */
  public function getSettingsForm($requiredSelector);

}
