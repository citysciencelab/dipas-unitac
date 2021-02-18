<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Plugin\Masterportal\Controls;

/**
 * Defines a control plugin for the Orientation3D control structure.
 *
 * @ControlPlugin(
 *   id = "Orientation3D",
 *   title = @Translation("Display north pointing arrow"),
 *   description = @Translation("Control element that provides a control structure that points north all the time."),
 *   category = "display",
 *   configProperty = "orientation3d"
 * )
 */
class Orientation3D extends ControlPluginBase {
}
