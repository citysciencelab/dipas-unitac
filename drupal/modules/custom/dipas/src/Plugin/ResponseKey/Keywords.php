<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\ResponseKey;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\dipas\Exception\MalformedRequestException;

/**
 * Class Keywords.
 *
 * @ResponseKey(
 *   id = "keywords",
 *   description = @Translation("Requests the keywords for a given text string."),
 *   requestMethods = {
 *     "POST",
 *   },
 *  isCacheable = false
 * )
 *
 * @package Drupal\dipas\Plugin\ResponseKey
 */
class Keywords extends ResponseKeyBase {

  /**
   * @var \Drupal\dipas\Service\DipasKeywordsInterface
   */
  protected $keywordService;

  /**
   * {@inheritdoc}
   */
  protected function setAdditionalDependencies(ContainerInterface $container) {
    $this->keywordService = $container->get('dipas.keywords');
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginResponse() {
    if ($data = json_decode($this->currentRequest->getContent())) {
      if (array_key_exists('description', $data)) {
        return [
          'keywords' => $this->keywordService->getRequestKeywords($data->description),
          'token' => $this->keywordService->getToken(),
        ];
      }
      else {
        $this->logger->error("Parameter 'description' missing in POST data");
        throw new MalformedRequestException("Request data could not be decoded! Parameter 'description' missing in POST data.", 400);
      }
    }
    else {
      $this->logger->error(sprintf("Could not decode POST data. Original data transferred: %s", (string) $this->currentRequest->getContent()));
      throw new MalformedRequestException("Request data could not be decoded!", 400);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getResponseKeyCacheTags() {
    return [];
  }
}
