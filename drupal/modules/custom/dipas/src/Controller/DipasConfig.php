<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Controller;

use Drupal\dipas\Service\DipasConfigInterface;

/**
 * Class DipasConfig.
 *
 * @todo Remove this class in favor of \Drupal\dipas\Service\DipasConfig.
 *    The #get() Method with nested array access is directly supported by the
 *    {@link \Drupal\Core\Config\Config} Class.
 *
 * @depredated Use the {@link \Drupal\dipas\Service\DipasConfig} Service
 *   instead. Be aware that the nested access key must be separated by "."
 *   instead of "/".
 *
 * @package Drupal\dipas\Controller
 */
class DipasConfig {

  /**
   * The DIPAS configuration service.
   *
   * @var \Drupal\dipas\Service\DipasConfigInterface
   */
  protected $config;

  /**
   * Constructor.
   *
   * @param \Drupal\dipas\Service\DipasConfigInterface $config
   *   The DIPAS configuration service.
   */
  public function __construct(DipasConfigInterface $config) {
    $this->config = $config;
  }

  /**
   * Returns the config value for the given key.
   *
   * @param string $key
   *   The key to retrieve. Supports nested access with "/" as delimiter.
   *
   * @return \Drupal\Core\Config\ImmutableConfig|false
   *   The config object associated with the given key or false if not found.
   */
  public function get($key) {
    $key = explode('/', $key);
    if ($value = $this->config->get(array_shift($key))) {
      while ($subkey = array_shift($key)) {
        if (isset($value[$subkey])) {
          $value = $value[$subkey];
        }
        else {
          return FALSE;
        }
      }
      return $value;
    }
    return FALSE;
  }

}
