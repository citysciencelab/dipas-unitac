<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal;

use Drupal\Core\Render\Element;

/**
 * Trait FindFormSectionTrait.
 *
 * @package Drupal\masterportal
 */
trait FindFormSectionTrait {

  /**
   * Recursively scans the form definition for a section with a given key.
   *
   * @param string $property
   *   The key to search.
   * @param array $form
   *   The form section definition to search.
   *
   * @return array|bool
   *   When found, the form section that matches the key, otherwise FALSE.
   */
  final protected function findSection($property, array &$form) {
    foreach (Element::children($form) as $key) {
      if ($key === $property && isset($form[$key]['multivaluePart'])) {
        return $form[$key];
      }
      elseif (($section = $this->findSection($property, $form[$key])) !== FALSE) {
        return $section;
      }
    }
    return FALSE;
  }

}
