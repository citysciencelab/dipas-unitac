<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\ResponseKey;

use Drupal\dipas\Exception\MalformedRequestException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ConfirmCookies.
 *
 * @ResponseKey(
 *   id = "confirmcookies",
 *   description = @Translation("Confirms the use of cookies."),
 *   requestMethods = {
 *     "POST",
 *   },
 *   isCacheable = false,
 *   shieldRequest = true
 * )
 *
 * @package Drupal\dipas\Plugin\ResponseKey
 */
class ConfirmCookies extends ResponseKeyBase {

  /**
   * @var \Drupal\dipas\Service\DipasCookieInterface
   */
  protected $dipasCookie;

  /**
   * {@inheritdoc}
   */
  protected function setAdditionalDependencies(ContainerInterface $container) {
    $this->dipasCookie = $container->get('dipas.cookie');
  }

  /**
   * {@inheritdoc}
   */
  protected function getResponseKeyCacheTags() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginResponse() {
    if (($content = json_decode($this->currentRequest->getContent())) && isset($content->confirmCookies)) {
      return [
        'message' => 'The use of cookies was confirmed.',
      ];
    }
    throw new MalformedRequestException('The use of cookies was not confirmed!', 400);
  }

  /**
   * {@inheritdoc}
   */
  public function getCookies() {
    $cookieData = $this->dipasCookie->getCookieData();
    if (!$cookieData) {
      $cookieData = new \stdClass();
    }
    $cookieData->cookiesConfirmed = gmdate('Y-m-d\TH:i:s\Z');
    $this->dipasCookie->setCookieData($cookieData);
    return [$this->dipasCookie->getCookie()];
  }

}
