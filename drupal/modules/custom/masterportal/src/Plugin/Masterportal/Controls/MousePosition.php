<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Plugin\Masterportal\Controls;

/**
 * Defines a control plugin implementation for the MousePosition display.
 *
 * @ControlPlugin(
 *   id = "Mouseposition",
 *   title = @Translation("Display mouse position"),
 *   description = @Translation("Control element that toggles the display of the mouzse coordinates."),
 *   category = "display",
 *   configProperty = "mousePosition"
 * )
 */
class MousePosition extends ControlPluginBase {
}
