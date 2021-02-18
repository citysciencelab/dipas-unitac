<?php

namespace Drupal\domain_dipas;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\domain\DomainNegotiatorInterface;
use Drupal\domain_access\DomainAccessManagerInterface;

/**
 * Class RenderBuilder.
 */
class ToolbarBuilder {

  use StringTranslationTrait;

  /**
   * The domain storage.
   *
   * @var \Drupal\domain\DomainStorageInterface
   */
  protected $domainStorage;

  /**
   * The domain negotiator.
   *
   * @var \Drupal\domain\DomainNegotiatorInterface
   */
  protected $domainNegotiator;

  /**
   * The domain access manager.
   *
   * @var \Drupal\domain_access\DomainAccessManagerInterface
   */
  protected $domainAccessManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * ToolbarBuilder constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\domain\DomainNegotiatorInterface $domain_negotiator
   *   The domain negotiator.
   * @param \Drupal\domain_access\DomainAccessManagerInterface $domain_access_manager
   *   The domain access manager.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    DomainNegotiatorInterface $domain_negotiator,
    DomainAccessManagerInterface $domain_access_manager,
    AccountProxyInterface $current_user
  ) {
    $this->domainNegotiator = $domain_negotiator;
    $this->domainStorage = $entity_type_manager->getStorage('domain');
    $this->domainAccessManager = $domain_access_manager;
    $this->currentUser = $current_user;
  }

  /**
   * Main build method.
   *
   * @return array
   *   Render array for the toolbar items.
   */
  public function build() {

    // Set cache.
    $items['domain_switcher'] = [
      '#cache' => [
        'contexts' => [
          'url',
          'user',
        ],
      ],
    ];

    // Build toolbar item and tray.
    $items['domain_switcher'] += [
      '#type' => 'toolbar_item',
      '#weight' => 999,
      'tab' => [
        '#type' => 'html_tag',
        '#tag' => 'a',
        '#attributes' => [
          'class' => [],
        ],
      ],
    ];
    $user = $this->currentUser;
    /** @var \Drupal\domain\Entity\Domain[] $domains */
    $domains = array_filter(
      $this->domainStorage->loadMultiple(),
      function ($domain) use ($user) {
        /** @var \Drupal\domain\Entity\Domain $domain */

        /*
         * Check if the current user is site_admin or has other
         * super admin powers.
         */
        if (
          count(array_intersect($user->getRoles(), [
            'siteadmin',
            'project_administrator_all_domains',
          ])) > 0
          || $user->hasPermission('administer domains')
        ) {
          $access = TRUE;
        }
        else {
          $access = $this->domainAccessManager->hasDomainPermissions($user, $domain, ['access inactive domains']);
        }

        return $access;
      }
    );
    $currentDomain = $this->domainNegotiator->getActiveDomain();

    // Build toolbar item and tray.
    $items['domain_switcher'] = NestedArray::mergeDeep(
      $items['domain_switcher'],
      [
        'tab' => [
          '#value' => $currentDomain ? $currentDomain->get('name') : 'default',
          '#attributes' => [
            'href' => '#',
            'title' => 'Test',
          ],
        ],
      ]
    );

    if (count($domains) > 1) {
      // Get links.
      $links = [];
      foreach ($domains as $domain) {
        $link = [
          'attributes' => [],
        ];
        $link_title = $domain->get('name');
        if ($domain->id() === $currentDomain->id()) {
          $link['attributes']['class'][] = 'is-active';
          $link['attributes']['title'] = $this->t(
            'Current active @current proceeding',
            ['@current' => $link_title]
          );
          $url_options['fragment'] = '!';
        }
        $link['title'] = $link_title;
        $link['url'] = Url::fromUri($domain->getUrl(), ['absolute' => TRUE]);
        $links[] = $link;
      }

      $items['domain_switcher']['tray'] = [
        '#heading' => $this->t('Admin Toolbar Domain Switcher'),
        'content' => [
          '#theme' => 'links',
          '#links' => $links,
          '#attributes' => [
            'class' => ['toolbar-menu'],
          ],
        ],
      ];
    }


    return $items;
  }

}
