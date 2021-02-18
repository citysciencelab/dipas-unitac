<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\SidebarBlock;

/**
 * Class Newsletter
 *
 * @SidebarBlock(
 *   id = "newsletter",
 *   name = @Translation("Newsletter subscription"),
 *   description = @Translation("Displays a newsletter subscription form of a 3rd party provider in a block")
 * )
 *
 * @package Drupal\dipas\Plugin\SidebarBlock
 */
class Newsletter extends SidebarBlockBase {

  /**
   * {@inheritdoc}
   */
  public function getSettingsForm($requiredSelector) {
    return [
      'snippet' => [
        '#type' => 'textarea',
        '#title' => $this->t('JavaScript snippet', [], ['context' => 'DIPAS']),
        '#default_value' => isset($this->settings['snippet']) ? $this->settings['snippet'] : '',
        '#states' => [
          'required' => [$requiredSelector => ['checked' => TRUE]],
        ],
      ],
    ];
  }

}
