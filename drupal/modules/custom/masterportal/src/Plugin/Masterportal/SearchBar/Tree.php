<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Plugin\Masterportal\SearchBar;

use Drupal\Core\Form\FormStateInterface;
use Drupal\masterportal\Plugin\Masterportal\PluginBase;
use Drupal\masterportal\PluginSystem\SearchBarPluginInterface;

/**
 * Defines a SearchBar plugin implementation for Gazetteer.
 *
 * @SearchBarPlugin(
 *   id = "Tree",
 *   title = @Translation("Tree"),
 *   description = @Translation("A search bar plugin to utilize a tree search."),
 *   configProperty = "tree"
 * )
 */
class Tree extends PluginBase implements SearchBarPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function getDefaults() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(FormStateInterface $form_state, $dependantSelector = FALSE, $dependantSelectorProperty = NULL, $dependantSelectorValue = NULL) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function injectConfiguration(\stdClass &$pluginSection) {}

}
