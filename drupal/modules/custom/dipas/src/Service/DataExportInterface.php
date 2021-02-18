<?php

namespace Drupal\dipas\Service;

interface DataExportInterface {

  /**
   * Route handler callback method.
   *
   * @param string $type
   *   The type of the desired export:
   *   [contributions|contribution_comments|conception_comments].
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The export data as a file download (CSV).
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function export($type);

}
