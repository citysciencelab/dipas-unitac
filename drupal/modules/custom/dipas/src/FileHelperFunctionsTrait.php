<?php

namespace Drupal\dipas;

use Drupal\Core\Entity\Query\QueryInterface;

/**
 * FileHelperFunctionsTrait.
 *
 * @package Drupal\dipas
 */
trait FileHelperFunctionsTrait {

  /**
   * Returns a list of all files the user can download.
   *
   * List the name, the url, the mimetype and the size.
   *
   * @return array
   *   array of all media entities of type 'download' with detail information
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getDownloadPathFromEntities($conditions = []) {
    $entityQuery = $this->entityTypeManager->getStorage('media')->getQuery();
    $entityQuery->condition('bundle', 'download', '=');

    if ($this->isDomainModuleInstalled()) {
      if ($this->listingIsDomainSensitive()) {
        $this->makeEntityQueryDomainSensitive($entityQuery);
      }
      else {
        $entityQuery->condition('field_domain_access', $this->getProceedingIDs('visible'), 'IN');
      }
    }

    foreach ($conditions as $condition) {
      $entityQuery->condition($condition['field'], $condition['value'], $condition['operator']);
    }

    $media_entity_id_list = $entityQuery->execute();

    $file_urls = [];

    foreach ($media_entity_id_list as $media_entity_id => $value) {
      $media_entity = $this->entityTypeManager->getStorage('media')
        ->load($media_entity_id);

      $file_fid = $media_entity->get('field_media_file')
        ->first()
        ->get('target_id')
        ->getString();

      $file_name = $media_entity->getName();
      $file = $this->entityTypeManager->getStorage('file')->load($file_fid);

      $file_data = [
        'name' => $file_name,
        'url' => file_create_url($this->getFileUriFromFileId($file_fid)),
        'mimetype' => $file->getMimeType(),
        'size' => $file->getSize(),
        'fordoc' => (boolean) (($flag = $media_entity->get('field_serve_for_documentation')->first()) ? $flag->get('value')->getString() : FALSE),
        'upload_date' => $this->convertTimestampToUTCDateTimeString($file->getCreatedTime(), FALSE),
      ];

      // Get domain specific data.
      $proceedingid = $media_entity->get('field_domain_access')->target_id;
      $proceedingConfig = $this->getConfig($proceedingid);
      $file_data['proceedingid'] = $proceedingid;
      $file_data['proceedingname'] = $proceedingConfig->get('ProjectInformation.site_name');

      $file_urls[] = $file_data;
    }

    array_walk($file_urls, function (&$file_url) {
      $this->postProcessDataRow($file_url);
    });

    return $file_urls;
  }

  /**
   * Extracts the wrapper uri from a file id.
   *
   * @param int $fid
   *   The file id.
   *
   * @return string
   *   The file wrapper URI.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getFileUriFromFileId($fid) {
    $file = $this->entityTypeManager->getStorage('file')->load($fid);
    return $file->get('uri')->first()->getString();
  }

  /**
   * Called on each data row returned by the query.
   *
   * @param array $row
   *   The currently processed data row.
   */
  protected function postProcessDataRow(array &$row) {
  }

  /**
   * Formats a given DateTime object into an UTC datetime string.
   *
   * @param int $timestamp
   *   The timestamp.
   * @param bool $isUTC
   *   Flag if the timestamp is in UTC-Format.
   *
   * @return string
   *   The timestamp in an UTC Format
   *
   * @throws \Exception
   */
  abstract protected function convertTimestampToUTCDateTimeString($timestamp, $isUTC);

  /**
   * Helper function to retrieve the proceeding configuration object.
   *
   * @param string $domainid
   *   The proceeding id.
   *
   * @return \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   *   The desired configuration object.
   */
  abstract protected function getConfig($domainid);

  abstract protected function isDomainModuleInstalled();

  abstract protected function getActiveDomain();

  abstract protected function makeEntityQueryDomainSensitive(QueryInterface &$query);

  abstract protected function listingIsDomainSensitive($isSensitive = NULL);

  abstract protected function getProceedingIDs($onlyVisible = FALSE);

}
