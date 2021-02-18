<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Plugin\Masterportal\Controls;

/**
 * Defines a control plugin implementation for the freeze toggle button.
 *
 * @ControlPlugin(
 *   id = "Freeze",
 *   title = @Translation("Freeze"),
 *   description = @Translation("Control element that freezes the display of the Masterportal in it's current state."),
 *   category = "button",
 *   configProperty = "freeze"
 * )
 */
class Freeze extends ControlPluginBase {
}
