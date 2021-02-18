<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a PDSResponse annotation object.
 *
 * Plugin Namespace: Plugin\PDSResponse.
 *
 * @see \Drupal\dipas\PluginSystem\PDSResponsePluginManager
 * @see \Drupal\dipas\PluginSystem\PDSResponsePluginInterface
 * @see plugin_api
 *
 * @Annotation
 */
class PDSResponse extends Plugin {

  /**
   * The response key.
   *
   * @var string
   */
  public $id;

  /**
   * The description of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description;

  /**
   * Allowed request methods for this plugin.
   *
   * @var array
   */
  public $requestMethods;

  /**
   * Can the contents of this plugin be cached?
   *
   * @var bool
   */
  public $isCacheable;

}
