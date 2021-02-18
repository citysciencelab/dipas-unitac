<?php


namespace Drupal\dipas\Plugin\SettingsSection;

use Drupal\masterportal\DomainAwareTrait;

/**
 * Trait NodeSelectionTrait.
 *
 * The trait provides a list of all medias available on the current domain, keyed
 * by the media id.
 *
 * @package Drupal\dipas\Plugin\SettingsSection
 */
trait MediaSelectionTrait {

  use DomainAwareTrait;

  /** @var \Drupal\media\MediaStorage */
  protected $mediaStorage;

  /**
   * Returns an array of page titles keyed by media id.
   *
   * The list includes all 'page' medias available on the current domain, keyed
   * by the media id. The first item of the list is a "Please select" entry.
   *
   * @return array
   */
  protected function getMediaOptions($type = NULL) {
    $mediaOptions = drupal_static('dipas_media_options_' . $type, NULL);

    if (is_null($mediaOptions)) {
      // Fetch all pages for this proceeding.
      $query = $this->mediaStorage->getQuery();
      $query->condition('status', 1, '=');

      if ($type !== NULL) {
        $query->condition('bundle', $type, '=');
      }

      if ($this->isDomainModuleInstalled()) {
        $domainConditions = $query->orConditionGroup();
        $domainConditions->condition('field_domain_access', $this->getActiveDomain(), '=');
        $domainConditions->condition('field_domain_all_affiliates', '1', '=');
        $query->condition($domainConditions);
      }

      $mediaIDs = $query->execute();
      $mediaOptions = [
        '' => $this->t('Please choose'),
      ];

      foreach ($this->mediaStorage->loadMultiple($mediaIDs) as $media) {
        $mediaOptions[$media->id()] = sprintf('%s (Media-ID %d)', $media->label(), $media->id());
      }
    }

    return $mediaOptions;
  }

}
