<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\PluginSystem;

use Drupal\Core\Form\FormStateInterface;

/**
 * Interface SettingsSectionPluginInterface.
 *
 * Defined the API for configuration section plugins.
 *
 * @package Drupal\dipas\PluginSystem
 */
interface SettingsSectionPluginInterface {

  /**
   * Provide default values for the settings contained.
   *
   * @return array
   *   The default settings, keyed by field name.
   */
  public static function getDefaults();

  /**
   * Return the form portion definition for this plugin.
   *
   * @param array $form
   *   The complete form as defined prior to this plugin.
   * @param FormStateInterface $form_state
   *   The FormState object of the form.
   *
   * @return array
   *   The form definition for this plugin.
   */
  public function getForm(array $form, FormStateInterface $form_state);

  /**
   * Returns the refined/processed configuration values.
   *
   * These values are stored in the site's configuration and are also
   * given to this plugin upon instantiating it.
   *
   * @param array $plugin_values
   *   The section of the form values that belong to the plugin.
   * @param array $form_values
   *   The raw form values.
   *
   * @return array
   *   The processed values ready to be stored.
   */
  public function getProcessedConfigurationValues(array $plugin_values, array $form_values);

  /**
   * Method to re-set the plugin values.
   *
   * @param array $values
   *   The values to set, keyed by the property name.
   */
  public function setValues(array $values);

  /**
   * Act upon submitted values.
   *
   * Submitted values will be set as the plugins values.
   */
  public function onSubmit();

  /**
   * Extract the processed values from the raw form values.
   *
   * @param array $plugin_values
   *   The plugin section of the form values.
   * @param array $form_values
   *   The raw form values.
   *
   * @return array
   *   The processed/refined value array.
   */
  public static function getProcessedValues(array $plugin_values, array $form_values);

  /**
   * Returns a flag if any settings should get saved to config.
   *
   * @return bool
   *   TRUE if yes, FALSE for "display-only" sections without setting data.
   */
  public function hasConfigurationSettings();

}
