<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a SidebarBlock annotation object.
 *
 * Plugin Namespace: Plugin\SidebarBlock.
 *
 * @see \Drupal\dipas\PluginSystem\SidebarBlockPluginManager
 * @see \Drupal\dipas\PluginSystem\SidebarBlockPluginInterface
 * @see plugin_api
 *
 * @Annotation
 */
class SidebarBlock extends Plugin {

  /**
   * The block id.
   *
   * @var string
   */
  public $id;

  /**
   * The block name.
   *
   * @var string
   */
  public $name;

  /**
   * The description of the block.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description;

}
