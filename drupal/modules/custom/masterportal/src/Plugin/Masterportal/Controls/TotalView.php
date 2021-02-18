<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Plugin\Masterportal\Controls;

/**
 * Defines a control plugin implementation for the TotalView button.
 *
 * @ControlPlugin(
 *   id = "TotalView",
 *   title = @Translation("Reset viewport"),
 *   description = @Translation("Resets the viewport of the Masterportal to it's initial state."),
 *   category = "button",
 *   configProperty = "totalView"
 * )
 */
class TotalView extends ControlPluginBase {
}
