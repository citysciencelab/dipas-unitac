<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal;

/**
 * Trait EnsureObjectStructureTrait.
 *
 * @package Drupal\masterportal
 */
trait EnsureObjectStructureTrait {

  /**
   * Ensures a path exists in the configuration object given.
   *
   * Path syntax:
   * 'property->subProperty->[subPropA, subPropB]->[subPropX, *subPropY]'
   *
   * ensures/creates
   *
   * $config->property->subProperty->subPropA->subPropX
   * $config->property->subProperty->subPropA->subPropY[]
   * $config->property->subProperty->subPropB->subPropX
   * $config->property->subProperty->subPropB->subPropY[]
   *
   * @param mixed &$config
   *   The config object.
   * @param string $path
   *   The path(s) to ensure.
   */
  protected static function ensureConfigPath(&$config, $path) {

    // Explode the path given in it's parts.
    $parts = explode('->', $path);

    // Extract and store the current stage to ensure/create.
    $currrent = array_shift($parts);

    // What remains of the path?
    $remaining = implode('->', $parts);

    // Consists the current stage of multiple subPaths?
    if (preg_match('~\[.+\]~', $currrent)) {
      $currrent = preg_replace('~[\[\]]~', '', $currrent);
      $currrent = explode(',', $currrent);
      array_walk($currrent, function (&$part) {
        $part = trim(str_replace('"', '', $part));
      });
    }
    if (!is_array($currrent)) {
      $currrent = [$currrent];
    }

    // Create/ensure each current stage.
    foreach ($currrent as $subpath) {

      // Create an object or an array?
      if (preg_match('~^\*~', $subpath)) {
        $subpath = substr($subpath, 1);
        $create = [];
      }
      else {
        $create = new \stdClass();
      }

      // Actually create/ensure the subpath.
      if (
        (is_array($config) && !isset($config[$subpath])) ||
        (is_object($config) && !isset($config->{$subpath}))
      ) {
        if (is_object($config)) {
          $config->{$subpath} = $create;
        }
        else {
          $config[$subpath] = $create;
        }
      }

      // When there is something remaining, recurse.
      if (!empty($remaining)) {
        if (is_object($config)) {
          static::ensureConfigPath($config->{$subpath}, $remaining);
        }
        else {
          static::ensureConfigPath($config[$subpath], $remaining);
        }

      }

    }

  }

}
