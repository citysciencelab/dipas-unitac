<?php

namespace Drupal\dipas_stories\Plugin\Masterportal\MapFeature;

use Drupal\dipas_stories\Annotation\MasterportalMapFeature;
use Symfony\Component\HttpFoundation\Request;

/**
 * @MasterportalMapFeature(
 *   id="threedimensional",
 *   title=@Translation("3D"),
 *   description=@Translation("Display the Masterportal in 3D"),
 *   integrateInPreviewMode=true,
 *   libraries={"threedimensional"}
 * )
 */
class ThreeDimensional extends PluginBase {

  /**
   * {@inheritdoc}
   */
  public function modifyJsonConfiguration(\stdClass &$configuration, Request $currentRequest) {
    self::ensureConfigPath($configuration, 'Portalconfig->controls');

    $configuration->Portalconfig->controls = [
      "button3d" => true,
      "orientation3d" => true,
    ];
  }

}
