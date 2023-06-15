<?php

namespace Drupal\dipas_stories\PluginSystem;

use Drupal\Core\Form\FormStateInterface;

interface MasterportalSettingsSectionPluginInterface {

  /**
   * The form elements as used by the field widget.
   *
   * @param string $fieldname
   * @param string $pluginID
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param int $delta
   * @param array $pluginValues
   * @param array $fieldValue
   *
   * @return array
   */
  public function formElements($fieldname, $pluginID, FormStateInterface $form_state, $delta, array $pluginValues, array $fieldValue);

  /**
   * Process raw form values.
   *
   * @param array $rawValues
   *   Plugin values in an unprocessed form as submitted.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The FormState object of the submitted form.
   *
   * @return array
   *   The transformed plugin values ready to story.
   */
  public function massageFormValues(array $rawValues, FormStateInterface $form_state);

}
