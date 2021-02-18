<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Service;

/**
 * Interface MasterportalTokenServiceInterface.
 *
 * @package Drupal\masterportal\Service
 */
interface MasterportalTokenServiceInterface {

  /**
   * Returns a message of type string containing all available tokens.
   *
   * @param array $exclude
   *   Tokens to exclude from the available token list.
   * @param array $additional
   *   Array of additional available tokens.
   * @param bool $allTokens
   *   Should only the additional tokens be listed?
   *
   * @return string
   *   The available tokens message.
   */
  public function availableTokens($exclude = [], $additional = [], $allTokens = TRUE);

  /**
   * Replaces custom Masterportal tokens with their respective dynamic content.
   *
   * @param string $subject
   *   The text in which to replace the tokens.
   * @param array $tokens
   *   A set of custom tokens. If empty, the standard tokens will be used.
   * @param bool $allTokens
   *   Should all available tokens be utilized or just the path tokens?
   * @param array $preservedTokens
   *   Tokens that should get preserved.
   *
   * @return string
   *   The processed text.
   */
  public function replaceTokens($subject, array $tokens = [], $allTokens = TRUE, $preservedTokens = []);

  /**
   * Determines if a given string contains any tokens defined.
   *
   * @param string $subject
   *   The text in which to replace the tokens.
   *
   * @return bool
   *   TRUE if tokens are contained, FALSE otherwise.
   */
  public function containsTokens($subject);

  /**
   * Returns the tokens used for configuration file paths.
   *
   * @param array $exclude
   *   Tokens to exclude from the available token list.
   * @param bool $allTokens
   *   Should all available tokens be utilized or just the path tokens?
   * @param bool $replace
   *   Flag indicating if the tokens should be replaced or just listed.
   * @param string $subject
   *   If $return is TRUE, the string in which to replace the tokens.
   *
   * @return array
   *   The tokens.
   */
  public function pathTokens($exclude = [], $allTokens = FALSE, $replace = FALSE, $subject = '');

  /**
   * Returns a token category.
   *
   * @param string $type
   *   The type of tokens to return (simple or path).
   * @param array $exclude
   *   Tokens to exclude.
   *
   * @return array
   *   The tokens.
   */
  public function getTokens($type = 'all', array $exclude = []);

  /**
   * Sets the token service to use file system or browser paths.
   *
   * @param bool $useFileSystemPaths
   *   Should filesystem paths be returnd for file sensitive tokens?
   *
   * @return void
   */
  public function setFileSystemTokenReplacement($useFileSystemPaths = FALSE);

}
