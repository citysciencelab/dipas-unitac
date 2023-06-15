<?php

namespace Drupal\dipas_stories\PluginSystem;

use Symfony\Component\HttpFoundation\Request;

interface MasterportalMapFeaturePluginInterface {

  /**
   * Allows the plugin to act on the Masterportal JavaScript configuration file.
   *
   * @param \stdClass $configuration
   *   The javascript configuration contents as an object
   * @param \Symfony\Component\HttpFoundation\Request $currentRequest
   *   The currently processed request
   *
   * @return void
   */
  public function modifyJavaScriptConfiguration(\stdClass &$configuration, Request $currentRequest);

  /**
   * Allows the plugin to act on the Masterportal  Json configuration file.
   *
   * @param \stdClass $configuration
   *   The Json configuration as an object
   * @param \Symfony\Component\HttpFoundation\Request $currentRequest
   *   The currently processed request
   *
   * @return void
   */
  public function modifyJsonConfiguration(\stdClass &$configuration, Request $currentRequest);

  /**
   * Allows the plugin to modify the story configuration file.
   *
   * @param \stdClass $configuration
   *   The story configuration json as an object.
   *
   * @return void
   */
  public function modifyStoryConfiguration(\stdClass &$configuration);

}
