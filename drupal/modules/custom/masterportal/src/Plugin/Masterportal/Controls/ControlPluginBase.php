<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Plugin\Masterportal\Controls;

use Drupal\Core\Form\FormStateInterface;
use Drupal\masterportal\Plugin\Masterportal\PluginBase;
use Drupal\masterportal\PluginSystem\ControlPluginInterface;

/**
 * Class ControlPluginBase.
 *
 * Most control plugins are only a single checkbox without any settings. So
 * this class provides for the base functionality and spares this hassle for
 * the simple control plugins.
 *
 * @package Drupal\masterportal\Plugin\Masterportal\Controls
 */
abstract class ControlPluginBase extends PluginBase implements ControlPluginInterface {

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
  public function injectJavascriptConfiguration(\stdClass &$config) {
  }

  /**
   * {@inheritdoc}
   */
  public static function hasJsonConfiguration() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public static function hasJavascriptConfiguration() {
    return FALSE;
  }

}
