<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\ResponseKey;

use Drupal\dipas\Exception\MalformedRequestException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class ContributionNodeRequestBase extends ResponseKeyBase {

  /**
   * Checks for required parameters and throws exceptions is parameters are missing.
   *
   * @param int $nid
   *   Contribution node ID (fetched from request URL if not provided)
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\dipas\Exception\MalformedRequestException
   */
  protected function checkRequest($nid = NULL) {
    $id = is_null($nid) ? $this->currentRequest->attributes->get('id') : $nid;
    if (is_numeric($id) && !(($node = $this->getNode($id)))) {
      throw new MalformedRequestException('The id given is invalid!', 400);
    }
    else if (is_null($this->getNode())) {
      throw new NotFoundHttpException();
    }
  }

  /**
   * Loads and returns a node (singleton).
   *
   * @param int $nid
   *   The node id.
   *
   * @return \Drupal\node\NodeInterface
   *   The loaded node object.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getNode($nid = 0) {
    static $node = NULL;
    if (is_null($node)) {
      /* @var \Drupal\node\NodeInterface $node */
      $node = $this->entityTypeManager->getStorage('node')->load($nid);
    }
    return $node;
  }

}
