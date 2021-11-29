<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a CockpitDataResponse annotation object.
 *
 * Plugin Namespace: Plugin\CockpitDataResponse.
 *
 * @see \Drupal\dipas\PluginSystem\CockpitDataResponsePluginManager
 * @see \Drupal\dipas\PluginSystem\CockpitDataResponsePluginInterface
 * @see plugin_api
 *
 * @Annotation
 */
class CockpitDataResponse extends Plugin {

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

  /**
   * The maximum time of the cache in minutes.
   *
   * @var number
   */
  public $maxAge;

}
