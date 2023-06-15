<?php

namespace Drupal\domain_dipas\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\masterportal\Form\DomainEdit;

/**
 * Base form for domain edit forms.
 *
 * @property \Drupal\domain\Entity\Domain $entity
 */
class DomainEditForm extends DomainEdit {

  /**
   * {@inheritDoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    if (
      $this->isDefaultDomainCreation()
      || $this->entity->isDefault()
    ) {
      /*
       * The form for the first domain must be treated differently.
       * The machine name must be default. For the rest of fields we set
       * appropriate defaults.
       */
      $form['id']['#disabled'] = TRUE;
      unset($form['id']['#machine_name']);

      $form['is_default']['#type'] = 'hidden';
      $form['is_default']['#default_value'] = TRUE;

      $form['scheme']['#type'] = 'hidden';
      $form['scheme']['#default_value'] = 'variable';

      $form['weight']['#type'] = 'hidden';

      $form['validate_url']['#type'] = 'hidden';
      $form['validate_url']['#default_value'] = FALSE;

      $form['status']['#type'] = 'hidden';

      // Set default values if the entity is new.
      if ($this->entity->isNew()) {
        $form['id']['#default_value'] = 'default';
        $form['name']['#default_vaulue'] = 'DIPAS';
      }

    }
    else {
      /*
       * Alter the form for the creation of new domains.
       */

      $form['id']['#type'] = 'hidden';

      $form['hostname']['#default_value'] = $this->entity->isNew() ?
        '' :
        $this->stripHostname($this->entity->getHostname());

      $form['hostname']['#field_suffix'] = '.' . $this->getDefaultHostname();

      $form['is_default']['#type'] = 'hidden';
      $form['is_default']['#default_value'] = FALSE;

      $form['scheme']['#type'] = 'hidden';
      $form['scheme']['#default_value'] = 'variable';

      $form['weight']['#type'] = 'hidden';

      $form['validate_url']['#type'] = 'hidden';
      $form['validate_url']['#default_value'] = FALSE;

      if ($this->entity->isNew()) {
        $form['name']['#default_value'] = '';
      }
    }

    return $form;
  }

  /**
   * Determine if this form is about to create the fist/default domain.
   *
   * @return bool
   *   True if this is the first domain creation.
   */
  protected function isDefaultDomainCreation() {
    $domains = $this->domainStorage->loadMultiple();
    return count($domains) === 0;
  }

  /**
   * Retrieves the hostname of the default domain if present.
   *
   * @return string
   *   The hostname of the default domain or an empty string
   */
  protected function getDefaultHostname() {
    /** @var \Drupal\domain\Entity\Domain $defaultDomain */
    if ($defaultDomain = $this->domainStorage->loadDefaultDomain()) {
      return $defaultDomain->getHostname();
    }
    return '';
  }

  /**
   * {@inheritDoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    if (!$this->isDefaultDomainCreation() && $form_state->getValue('id') !== 'default') {

      /** @var \Drupal\domain\DomainInterface $entity */
      $entity = $this->entity;

      $hostnamePrefix = $form_state->getValue('hostname');
      $hostname = $hostnamePrefix . $form['hostname']['#field_suffix'];

      $form_state->setValue('hostname', $hostname);
      $form_state->setValue('id', $hostnamePrefix);

      $entity->setHostname($hostname);
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * Strips the default domain from the hostname and returns only the prefix.
   *
   * @param string $hostname
   *   The hostname to strip.
   *
   * @return string
   *   The stripped subdomain prefix.
   */
  protected function stripHostname(string $hostname) {
    return str_replace('.' . $this->getDefaultHostname(), '', $hostname);
  }

}
