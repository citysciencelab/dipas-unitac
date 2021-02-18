<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\SidebarBlock;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\dipas\PluginSystem\SidebarBlockPluginInterface;

/**
 * Class SidebarBlockBase
 *
 * @package Drupal\dipas\Plugin\SidebarBlock
 */
abstract class SidebarBlockBase implements SidebarBlockPluginInterface {

  use StringTranslationTrait;

  /**
   * @var array
   */
  protected $settings;

  /**
   * SidebarBlockBase constructor.
   *
   * @param array $settings
   */
  public function __construct(array $settings) {
    $this->settings = $settings;
  }

  /**
   * {@inheritdoc}
   */
  public static function getDefaultSettings() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getSettingsForm($requiredSelector) {
    return [];
  }

}
