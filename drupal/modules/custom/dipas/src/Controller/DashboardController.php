<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

/**
 * Class DashBoardController
 *
 * @package Drupal\dipas\Controller
 */
class DashboardController extends ControllerBase {

  /**
   * Creates the render array for the dashboard page
   *
   * @return array
   */
  public function viewDashboard() {
    return [
      '#theme' => 'dipas_dashboard',
      '#attached' => [
        'library' => ['dipas/dipas_dashboard']
      ],
      '#create_links' => [
        '#theme' => 'links',
        '#links' => [
          [
            'title' => [
              '#markup' => sprintf("<span class='link_title'>%s</span>", $this->t('Page'))
            ],
            'url' => Url::fromRoute('node.add', ['node_type' => 'page']),
            'attributes' => [
              'class' => [
                'iconLink',
                'create_page',
              ],
            ],
          ],
          [
            'title' => [
              '#markup' => sprintf("<span class='link_title'>%s</span>", $this->t('Conception'))
            ],
            'url' => Url::fromRoute('node.add', ['node_type' => 'conception']),
            'attributes' => [
              'class' => [
                'iconLink',
                'create_conception',
              ],
            ],
          ],
          [
            'title' => [
              '#markup' => sprintf("<span class='link_title'>%s</span>", $this->t('Appointment'))
            ],
            'url' => Url::fromRoute('node.add', ['node_type' => 'appointment']),
            'attributes' => [
              'class' => [
                'iconLink',
                'create_appointment',
              ],
            ],
          ],
          [
            'title' => [
              '#markup' => sprintf("<span class='link_title'>%s</span>", $this->t('Image'))
            ],
            'url' => Url::fromRoute('entity.media.add_form', ['media_type' => 'image']),
            'attributes' => [
              'class' => [
                'iconLink',
                'create_image',
              ],
            ],
          ],
          [
            'title' => [
              '#markup' => sprintf("<span class='link_title'>%s</span>", $this->t('Download'))
            ],
            'url' => Url::fromRoute('entity.media.add_form', ['media_type' => 'download']),
            'attributes' => [
              'class' => [
                'iconLink',
                'create_download',
              ],
            ],
          ],
          [
            'title' => [
              '#markup' => sprintf("<span class='link_title'>%s</span>", $this->t('Logo'))
            ],
            'url' => Url::fromRoute('entity.media.add_form', ['media_type' => 'logo']),
            'attributes' => [
              'class' => [
                'iconLink',
                'create_logo',
              ],
            ],
          ],
        ],
      ],
      '#administer_links' => [
        '#theme' => 'links',
        '#links' => [
          [
            'title' => [
              '#markup' => sprintf("<span class='link_title'>%s</span>", $this->t('Contributions'))
            ],
            'url' => Url::fromRoute('view.contributions.admin_contribution_list'),
            'attributes' => [
              'class' => [
                'iconLink',
                'create_contribution',
              ],
            ],
          ],
          [
            'title' => [
              '#markup' => sprintf("<span class='link_title'>%s</span>", $this->t('Media'))
            ],
            'url' => Url::fromRoute('entity.media.collection'),
            'attributes' => [
              'class' => [
                'iconLink',
                'administer_media',
              ],
            ],
          ],
          [
            'title' => [
              '#markup' => sprintf("<span class='link_title'>%s</span>", $this->t('Categorization'))
            ],
            'url' => Url::fromRoute('entity.taxonomy_vocabulary.collection'),
            'attributes' => [
              'class' => [
                'iconLink',
                'administer_categorization',
              ],
            ],
          ],
          [
            'title' => [
              '#markup' => sprintf("<span class='link_title'>%s</span>", $this->t('Configuration'))
            ],
            'url' => Url::fromRoute('dipas.configform'),
            'attributes' => [
              'class' => [
                'iconLink',
                'administer_configuration',
              ],
            ],
          ],
        ],
      ],
    ];
  }
}
