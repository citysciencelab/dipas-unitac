<?php

namespace Drupal\dipas\Service;

use Drupal\Core\Config\Config;

/**
 * Interface DipasConfigInterface.
 *
 * @package Drupal\dipas\Service
 */
interface DipasConfigInterface {

  /**
   * Returns the configuration values stored under a given key.
   *
   * @param string $key
   *   The key the desired configuration is stored beneath.
   *
   * @return \Drupal\Core\Config\ImmutableConfig
   *   The configuration values stored under the key given.
   */
  public function get($key);

  /**
   * Returns the id of the used config object.
   *
   * @return string
   *   The id of the used config object.
   */
  public function id(): string;

  /**
   * Returns the mutable configuration object.
   *
   * @param string $id
   *   The id of the config object if not the default is desired.
   *
   * @return \Drupal\Core\Config\Config
   *   The configuration object.
   */
  public function getEditable($id = 'dipas.default.domain'): Config;

  /**
   * Returns the list of ids of all available dipas configurations.
   *
   * @return array
   *   The list of ids
   */
  public function getIds();

  /**
   * Returns the ID of the active domain.
   *
   * @return string
   */
  public function getConfigDomain();

}
