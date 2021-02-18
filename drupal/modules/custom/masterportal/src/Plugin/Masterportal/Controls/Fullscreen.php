<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Plugin\Masterportal\Controls;

/**
 * Defines a control plugin implementation for the fullscreen toggle button.
 *
 * @ControlPlugin(
 *   id = "Fullscreen",
 *   title = @Translation("Fullscreen"),
 *   description = @Translation("Control element that toggles the display of the Masterportal to full screen."),
 *   category = "button",
 *   configProperty = "fullScreen"
 * )
 */
class Fullscreen extends ControlPluginBase {
}
