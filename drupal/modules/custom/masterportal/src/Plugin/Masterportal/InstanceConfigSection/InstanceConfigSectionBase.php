<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Plugin\Masterportal\InstanceConfigSection;

use Drupal\Core\Form\FormStateInterface;
use Drupal\masterportal\Plugin\Masterportal\PluginBase;
use Drupal\masterportal\PluginSystem\InstanceConfigSectionInterface;

/**
 * Class InstanceConfigSectionBase.
 *
 * @package Drupal\masterportal\Plugin\Masterportal\InstanceConfigSection
 */
abstract class InstanceConfigSectionBase extends PluginBase implements InstanceConfigSectionInterface {

  /**
   * {@inheritdoc}
   */
  public function getForm(FormStateInterface $form_state, $dependantSelector = FALSE, $dependantSelectorProperty = NULL, $dependantSelectorValue = NULL) {
    return static::getFormSectionElements($form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function hasPostCompositionHook() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function postCompositionHook($type, \stdClass &$config) {
  }

}
