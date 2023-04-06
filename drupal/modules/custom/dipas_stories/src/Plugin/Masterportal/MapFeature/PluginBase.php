<?php

namespace Drupal\dipas_stories\Plugin\Masterportal\MapFeature;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\dipas_stories\PluginSystem\MasterportalMapFeaturePluginInterface;
use Drupal\masterportal\EnsureObjectStructureTrait;
use Symfony\Component\HttpFoundation\Request;

abstract class PluginBase implements MasterportalMapFeaturePluginInterface {

  use EnsureObjectStructureTrait, StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function modifyJavaScriptConfiguration(\stdClass &$configuration, Request $currentRequest) {}

  /**
   * {@inheritdoc}
   */
  public function modifyJsonConfiguration(\stdClass &$configuration, Request $currentRequest) {}

  /**
   * {@inheritdoc}
   */
  public function modifyStoryConfiguration(\stdClass &$configuration) {}

}
