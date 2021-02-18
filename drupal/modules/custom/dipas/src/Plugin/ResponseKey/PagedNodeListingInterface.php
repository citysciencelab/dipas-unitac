<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\ResponseKey;

interface PagedNodeListingInterface {

  /**
   * Value placeholder for "infinite" nodes per page.
   */
  const INFINITE_ITEMS_VALUE = 'inf';

  /**
   * Default nodes per page.
   */
  const DEFAULT_NODES_PER_PAGE = 10;

}
