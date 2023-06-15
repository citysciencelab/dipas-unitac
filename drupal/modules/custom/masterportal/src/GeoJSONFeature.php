<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal;

class GeoJSONFeature implements GeoJSONFeatureInterface {

  protected $feature;

  /**
   * GeoJSONFeature constructor.
   *
   * @param string $type
   */
  public function __construct($type = 'Feature', $id = FALSE) {
    $this->feature = new \stdClass();
    $this->feature->type = $type;
    $this->feature->geometry = new \stdClass();
    $this->feature->properties = new \stdClass();
    if ($id) {
      $this->setId($id);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setGeometryType($type) {
    $this->feature->geometry->type = $type;
  }

   /**
   * {@inheritdoc}
   */
  public function setId($id) {
    $this->feature->id = $id;
  }

  /**
   * {@inheritdoc}
   */
  public function addPoint(array $point) {
    $this->feature->geometry->coordinates = $point;
  }

  /**
   * {@inheritdoc}
   */
  public function addCoordinates(array $coordinates) {
    $this->feature->geometry->coordinates = $coordinates;
  }

  /**
   * {@inheritdoc}
   */
  public function setGeometry(\stdClass $geometry) {
    $this->feature->geometry = $geometry;
  }

  /**
   * {@inheritdoc}
   */
  public function addProperty($propertyName, $propertyValue) {
    $this->feature->properties->{$propertyName} = $propertyValue;
  }

  /**
   * {@inheritdoc}
   */
  public function getGeometry() {
    return $this->feature->geometry;
  }

  /**
   * {@inheritdoc}
   */
  public function getProperties() {
    return $this->feature->properties;
  }

  /**
   * {@inheritdoc}
   */
  public function getFeature() {
    return $this->feature;
  }

}
