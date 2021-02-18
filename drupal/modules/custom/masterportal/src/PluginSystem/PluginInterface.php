<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\PluginSystem;

use Drupal\Core\Form\FormStateInterface;

/**
 * Interface PluginInterface.
 *
 * @package Drupal\masterportal\PluginSystem
 */
interface PluginInterface {

  /**
   * Return the default values for settings.
   *
   * @return array
   *   The defaults.
   */
  public static function getDefaults();

  /**
   * Get the configuration form of the current plugin.
   *
   * @param FormStateInterface $form_state
   *   The FormStateInterface object.
   * @param string|false $dependantSelector
   *   Drupal Form API compatible states selector, if required fields are
   *   contained in the plugin's form definition. DO NOT set fields in this
   *   subform to required by default! This parameter is set to FALSE when
   *   no deoendant exists.
   * @param string $dependantSelectorProperty
   *   The property to check if a dependant exists.
   * @param mixed $dependantSelectorValue
   *   The value that the dependant has to match for our fields to
   *   mark as required.
   *
   * @return array|false
   *   A form definition array or FALSE, if there are no configuration options.
   */
  public function getForm(FormStateInterface $form_state, $dependantSelector = FALSE, $dependantSelectorProperty = NULL, $dependantSelectorValue = NULL);

  /**
   * Returns the configuration definition of a plugin in the configuration.
   *
   * @param FormStateInterface $form_state
   *   The FormStateInterface object.
   *
   * @return array
   *   The configuration array for this plugin to include in the portal's
   *   configuration file.
   */
  public function getConfigurationArray(FormStateInterface $form_state);

  /**
   * Inject the plugin configuration into the configuration object.
   *
   * @param \stdClass $pluginSection
   *   The configuration section to freely place settings in.
   */
  public function injectConfiguration(\stdClass &$pluginSection);

}
