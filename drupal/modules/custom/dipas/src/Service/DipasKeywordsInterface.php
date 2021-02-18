<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Service;

interface DipasKeywordsInterface {

  /**
   * Returns the keywords for a description text.
   *
   * @param string $description
   *   The text that should get analyzed by the keyword service.
   *
   * @return string[]
   *   Keyword strings matching the text given.
   */
  public function getRequestKeywords($description);

  /**
   * Returns the token for the response.
   *
   * @return string
   *   The token that identifies a keyword set for the save process.
   */
  public function getToken();

  /**
   * Save the selected keywords for a record with token $token.
   *
   * @param string $keywords
   *   The keywords that should get saved to the database.
   * @param string $token
   *   The token that was provided after querying the keyword suggestion service.
   *
   * @return boolean
   *   Success flag.
   */
  public function saveSelectedKeywords($keywords, $token, $contributionID);

}
