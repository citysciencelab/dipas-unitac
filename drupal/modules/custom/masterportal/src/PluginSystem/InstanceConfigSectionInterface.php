<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\PluginSystem;

use Drupal\Core\Form\FormStateInterface;

/**
 * Interface InstanceConfigSectionInterface.
 *
 * Defines the API of a section plugin for instance configuration sections.
 *
 * @package Drupal\masterportal\PluginSystem
 */
interface InstanceConfigSectionInterface {

  /**
   * Return the default values for settings.
   *
   * @return array
   *   The defaults.
   */
  public static function getDefaults();

  /**
   * Returns the form definition for the current section.
   *
   * @param FormStateInterface $form_state
   *   The FormStateInterface object of the whole form.
   *
   * @return array
   *   The form definition for the current section.
   */
  public function getFormSectionElements(FormStateInterface $form_state);

  /**
   * Returns the formatted plugin data for the instance configuration.
   *
   * @param array $rawFormData
   *   The raw form data.
   * @param FormStateInterface $form_state
   *   The FormStateInterface object of the whole form.
   *
   * @return array
   *   The configuration data of this plugin.
   */
  public function getSectionConfigArray(array $rawFormData, FormStateInterface $form_state);

  /**
   * Injects the current plugin configuration into the instance config.json.
   *
   * @param string $type
   *   Either config.json or config.js.
   * @param \stdClass $config
   *   The configuration object to modify.
   */
  public function injectSectionConfigurationSettings($type, \stdClass &$config);

  /**
   * Is there a post-process hook present?
   *
   * @return bool
   *   TRUE, if there is a post-process hook present.
   */
  public function hasPostCompositionHook();

  /**
   * Gets called after all section plugins have injected their config.
   *
   * @param string $type
   *   Either config.json or config.js.
   * @param \stdClass $config
   *   The configuration object to modify.
   */
  public function postCompositionHook($type, \stdClass &$config);

}
