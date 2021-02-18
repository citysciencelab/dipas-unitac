<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal;

/**
 * Interface GeoJSONFeatureInterface
 *
 * @package Drupal\masterportal
 */
interface GeoJSONFeatureInterface {

  /**
   * GeoJSONFeatureInterface constructor.
   *
   * @param string $type
   *   The type of GeoJSON element.
   */
  public function __construct($type = 'Feature');

  /**
   * Sets the geometry type.
   *
   * @param string $type
   *   The type of geometry.
   */
  public function setGeometryType($type);

  /**
   * Adds a single coordinate point according the the geometry type.
   *
   * @param array $point
   *   An array holding a pair of coordinates.
   */
  public function addPoint(array $point);

  /**
   * Sets an array of coordinates
   *
   * @param array[] $coordinates
   *   An array holding arrays of coordinates.
   */
  public function addCoordinates(array $coordinates);

  /**
   * Sets the whole geometry data at once.
   *
   * @param \stdClass $geometry
   *   The complete geometry structure data.
   */
  public function setGeometry(\stdClass $geometry);

  /**
   * Adds a single property dupel.
   *
   * @param $propertyName
   *   The name of the property.
   *
   * @param $propertyValue
   *   The value of the property.
   */
  public function addProperty($propertyName, $propertyValue);

  /**
   * Returns the geometry data.
   *
   * @return \stdClass
   */
  public function getGeometry();

  /**
   * Returns an array of properties.
   *
   * @return array
   *   The properties of this GeoJSON feature.
   */
  public function getProperties();

  /**
   * Returns the whole feature data.
   *
   * @return \stdClass
   */
  public function getFeature();

}
