<?php

namespace Drupal\domain_dipas\Access;

use Drupal\Core\Access\AccessCheckInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\domain\DomainNegotiatorInterface;
use Drupal\domain_access\DomainAccessManagerInterface;
use Symfony\Component\Routing\Route;

/**
 * Provides a global access check to ensure inactive domains are restricted.
 */
class DomainAccessCheck implements AccessCheckInterface {

  /**
   * The Domain negotiator.
   *
   * @var \Drupal\domain\DomainNegotiatorInterface
   */
  protected $domainNegotiator;

  /**
   * The domain access manager.
   *
   * @var \Drupal\domain_access\DomainAccessManagerInterface
   */
  protected $manager;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The path matcher service.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected $pathMatcher;

  /**
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Constructs the object.
   *
   * @param \Drupal\domain\DomainNegotiatorInterface $negotiator
   *   The domain negotiation service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Path\PathMatcherInterface $path_matcher
   *   The path matcher service.
   */
  public function __construct(
    DomainNegotiatorInterface $negotiator,
    ConfigFactoryInterface $config_factory,
    PathMatcherInterface $path_matcher,
    DomainAccessManagerInterface $manager,
    LoggerChannelInterface $logger
  ) {
    $this->domainNegotiator = $negotiator;
    $this->configFactory = $config_factory;
    $this->pathMatcher = $path_matcher;
    $this->manager = $manager;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(Route $route) {
    return $this->checkPath($route->getPath());
  }

  /**
   * {@inheritdoc}
   */
  public function checkPath($path) {
    $allowed_paths = $this->configFactory->get('domain.settings')
      ->get('login_paths');
    return !$this->pathMatcher->matchPath($path, $allowed_paths);
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account) {
    $domain = $this->domainNegotiator->getActiveDomain();
    // Is the domain allowed?
    // No domain, let it pass.
    if (empty($domain)) {
      return AccessResult::allowed()->addCacheTags(['url.site']);
    }

    if (
      $account->isAuthenticated()
      && in_array('project_admin', $account->getRoles())
      && !$this->manager->hasDomainPermissions($account, $domain, ['access inactive domains'])
    ) {
      // Returning an AccessResult::forbidden actually leads to a redirect loop.
      // Instead, we will simply end the session and redirect to the front page.
      $this->logger->notice(
        '@user was automatically logged out for trying to access the unassigned domain @domain.',
        [
          '@user' => $account->getAccountName(),
          '@domain' => $domain->id(),
        ]
      );
      user_logout();
      $response = new TrustedRedirectResponse(Url::fromRoute('user.login')->toString());
      $response->send();
    }

    return AccessResult::allowed()->addCacheTags(['url.site']);
  }

}
