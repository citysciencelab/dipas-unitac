<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Service;

use Drupal\Core\Extension\ExtensionPathResolver;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class MasterportalTokenService.
 *
 * Small custom service to replace common variables for the
 * Masterportal integration.
 *
 * @package Drupal\masterportal\Service
 */
class MasterportalTokenService implements MasterportalTokenServiceInterface {

  use StringTranslationTrait;

  /**
   * Holds all simple tokens and their respective values.
   *
   * @var array
   */
  protected $simpleTokens;

  /**
   * Holds all path tokens and their respective values.
   *
   * @var array
   */
  protected $pathTokens;

  /**
   * Holds all leyer related tokens and their respective values.
   *
   * @var array
   */
  protected $layerTokens;

  /**
   * Custom logging channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannel
   */
  protected $logger;

  /**
   * The currently processed request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * @var \Drupal\Core\Extension\ExtensionPathResolver
   */
  protected $extensionPathResolver;

  /**
   * Switch to determine if file-sensitive tokens should be replaced with file-system or browser paths.
   *
   * @var bool
   */
  protected $useFileSystemPaths = false;

  /**
   * MasterportalTokenService constructor.
   *
   * @param LoggerChannel $logger
   *   Custom logger channel.
   * @param RequestStack $request_stack
   *   Symfonys request stack.
   */
  public function __construct(
    LoggerChannel $logger,
    RequestStack $request_stack,
    ExtensionPathResolver $extension_path_resolver
  ) {
    $this->logger = $logger;
    // Determine the variable contents for the "masterportal_instance" variable.
    $this->currentRequest = $request_stack->getCurrentRequest();
    $this->extensionPathResolver = $extension_path_resolver;
    $this->setTokens();
  }

  protected function setTokens() {
    if (!empty($instance = $this->currentRequest->attributes->get('masterportal_instance'))) {
      /* @var \Drupal\masterportal\Entity\MasterportalInstanceInterface $instance_token */
      $instance_token = $instance->id();
    }
    else {
      $instance_token = 'none';
    }

    // Build a stack of dynamic variables for replacement
    // in the response content.
    $module_path = $this->extensionPathResolver->getPath('module', 'masterportal');

    $this->simpleTokens = [
      'base_path' => base_path(),
      'module_path' => $module_path,
      'library_path' => sprintf("%s%s/libraries/masterportal", $this->useFileSystemPaths ? '/' : base_path(), $module_path),
      'masterportal_instance' => $instance_token,
    ];
    $this->pathTokens = [
      'masterportal.css' => '{{library_path}}/css/masterportal.css',
      'masterportal.js' => '{{library_path}}/js/masterportal.js',
      'config.js' => '/masterportal/{{masterportal_instance}}/config.js?{{query_params}}',
      'config.json' => '/masterportal/{{masterportal_instance}}/config.json',
      'layerdefinitions.json' => '/masterportal/{{masterportal_instance}}/layerdefinitions.json?{{query_params}}',
      'services.json' => '/masterportal/services.json',
      'layerstyles.json' => '/masterportal/layerstyles.json',
    ];
    $this->layerTokens = [
      'query_params' => [$this, 'collectQueryParams'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setFileSystemTokenReplacement($useFileSystemPaths = FALSE) {
    $this->useFileSystemPaths = $useFileSystemPaths;
    $this->setTokens();
  }

  /**
   * {@inheritdoc}
   */
  public function availableTokens($exclude = [], $additional = [], $allTokens = TRUE) {
    $tokens = $this->mergeTokens($additional, TRUE, $allTokens);
    $tokens = array_filter(
      $tokens,
      function ($token) use ($exclude) {
        return !in_array($token, $exclude);
      }
    );
    $tokens = array_map(
      function ($token) {
        return sprintf('{{%s}}', $token);
      },
      $tokens
    );
    return sprintf($this->t('Available tokens', [], ['context' => 'Masterportal'])->__toString() . ': %s', implode(', ', $tokens));
  }

  /**
   * {@inheritdoc}
   */
  public function replaceTokens($subject, array $tokens = [], $allTokens = TRUE, $preservedTokens = []) {
    $tokens = $this->mergeTokens($tokens, FALSE, $allTokens);

    foreach ($tokens as $key => $replacement) {
      if (in_array($key, $preservedTokens)) {
        continue;
      }
      $pattern = sprintf('~\{\{%s\}\}~', preg_quote($key, '~'));

      if (preg_match($pattern, $subject)) {
        if (!is_string($replacement)) {
          try {
            [$object, $method] = $replacement;
            $replacement = $object->{$method}();
          }
          catch (\Exception $e) {
            $this->logger->error('Unknown token replacement value: @value', ['@value' => serialize($replacement)]);
          }
        }

        $subject = preg_replace($pattern, $replacement, $subject);
      }
    }
    return $subject;
  }

  /**
   * {@inheritdoc}
   */
  public function containsTokens($subject) {
    return preg_match('~\{\{[^\}]*?\}\}~', $subject);
  }

  /**
   * {@inheritdoc}
   */
  public function pathTokens($exclude = [], $allTokens = TRUE, $replace = FALSE, $subject = '') {
    return $replace
      ? $this->replaceTokens($subject, $this->pathTokens, $allTokens)
      : $this->availableTokens($exclude, $this->pathTokens, $allTokens);
  }

  /**
   * {@inheritdoc}
   */
  public function getTokens($type = 'all', array $exclude = []) {
    switch ($type) {
      case 'simple':
        $tokens = $this->simpleTokens;
        break;

      case 'path':
        $tokens = $this->pathTokens;
        break;

      case 'layer':
        $tokens = $this->layerTokens;
        break;

      default:
        $tokens = $this->mergeTokens(array_merge($this->pathTokens, $this->layerTokens));
    }
    $tokens = array_filter($tokens, function ($token) use ($exclude) {
      return !in_array($token, $exclude);
    }, ARRAY_FILTER_USE_KEY);
    return $tokens;
  }

  /**
   * Dynamic token replacement function.
   *
   * Collects the Query parameters and returns them as a string.
   */
  protected function collectQueryParams() {
    $query = $this->currentRequest->query->all();
    array_walk($query, function (&$item, $key) {
      $item = sprintf('%s=%s', $key, str_replace('"', '\\"', $item));
    });
    $replacement = implode('&', $query);
    return !empty($replacement)
      ? $replacement
      : '';
  }

  /**
   * Helper function to merge the token definitions.
   *
   * @param array $additional
   *   Additional tokens to mix in.
   * @param bool $keys
   *   Should the resulting array consist of the token keys or the complete definition?
   * @param bool $allTokens
   *   Should all or only the additional tokens be returned?
   *
   * @return array
   *   The result.
   */
  private function mergeTokens(array $additional, $keys = FALSE, $allTokens = TRUE) {
    if (!$allTokens) {
      return $keys
        ? array_keys($additional)
        : $additional;
    }
    return $keys
      ? array_merge(array_keys($this->simpleTokens), array_keys($additional))
      : array_merge($this->simpleTokens, $additional);
  }

}
