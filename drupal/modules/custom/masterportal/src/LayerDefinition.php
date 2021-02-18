<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal;

/**
 * Class LayerDefinition.
 *
 * Provides for the structured handling of layer definitions.
 *
 * @package Drupal\masterportal
 */
class LayerDefinition {

  /**
   * The layer definition array.
   *
   * @var array
   */
  protected $definition;

  /**
   * LayerDefinition constructor.
   *
   * @param \stdClass $data
   *   The layer definition object.
   */
  public function __construct(\stdClass $data) {
    $this->definition = $data;
  }

  /**
   * Returns the raw layer definition array.
   *
   * @return array
   *   The definition array.
   */
  public function getDefinition() {
    return $this->definition;
  }

  /**
   * Returns the layer id.
   *
   * @return string
   *   The id of the layer.
   */
  public function getId() {
    return $this->definition->id;
  }

  /**
   * Returns the layer name.
   *
   * @return string
   *   The name of the layer.
   */
  public function getName() {
    return $this->definition->name;
  }

  /**
   * Returns the layer URL.
   *
   * @return string
   *   The URL of the layer.
   */
  public function getUrl() {
    return $this->definition->url;
  }

}
