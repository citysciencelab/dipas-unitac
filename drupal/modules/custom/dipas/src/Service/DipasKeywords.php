<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Service;

use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\dipas\Controller\DipasConfig;
use Drupal\Core\Database\Connection;
use GuzzleHttp\ClientInterface;
use Drupal\dipas\Exception\StatusException;

/**
 * Class DipasKeywords
 *
 * @package Drupal\dipas\Service
 */
class DipasKeywords implements DipasKeywordsInterface {

  /**
   * The DIPAS configuration service.
   *
   * @var \Drupal\dipas\Controller\DipasConfig
   */
  protected $dipasConfig;

  /**
   * Custom logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;


  /**
   * The guzzle ClientInterface
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $guzzle;

  /**
   * static token to identify entries about keyword-sets in database.
   *
   * @var string
   */
  protected $token;

  /**
   * Drupal database Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $dbConnection;

  /**
   * Custom service to easy the handling of entities.
   *
   * @var \Drupal\dipas\Service\EntityServicesInterface
   */
  protected $entityServices;

  /**
   * DipasKeywords constructor.
   *
   * @param \Drupal\dipas\Controller\DipasConfig $config_factory
   *   Custom config service.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   Custom logger channel.
   * @param \GuzzleHttp\ClientInterface $guzzle
   *   The guzzle ClientInterface.
   */
  public function __construct(
    DipasConfig $config_factory,
    LoggerChannelInterface $logger,
    ClientInterface $guzzle,
    Connection $db_connection,
    EntityServicesInterface $entity_services
  ) {
    $this->dipasConfig = $config_factory;
    $this->logger = $logger;
    $this->guzzle = $guzzle;
    $this->dbConnection = $db_connection;
    $this->entityServices = $entity_services;
  }

  /**
   * {@inheritdoc}
   */
  public function getRequestKeywords($description) {
    if ($this->dipasConfig->get('KeywordSettings/enabled') === TRUE) {

      if ($description && strlen($description) > 2) {

        $time_before_request = microtime(TRUE);

        $keywords = $this->guzzle->request(
          'POST',
          $this->dipasConfig->get('KeywordSettings/service_url'),
          [
            'json' => [
              'description' => $description,
              'mode' => $this->dipasConfig->get('KeywordSettings/mode'),
              'externalService' => $this->dipasConfig->get('KeywordSettings/externalService'),
              'url' => 'http://keywordset.net', // Somehow this value must be set, seems not to be used at all...
              'num' => $this->dipasConfig->get('KeywordSettings/number_of_keywords'),
            ]
          ]
        );

        $request_duration = (microtime(TRUE) - $time_before_request) * 1000; // to get the request duration in milliseconds

        $response = ($result = json_decode($keywords->getBody())) ? $result : [];
      }

      // Create a static token which can be used to identify a database entry for this request
      $this->token = drupal_static('dipas_keywords_token', substr(md5(time()), 0, 10));

      // Store data to database and return the response
      $this->dbConnection->insert('dipas_keywords')
          ->fields([
            'token' => $this->token,
            'given_text' => $description,
            'keywords' => implode($response, ', '),
            'request_duration' => ceil($request_duration),
            'timeout' => time() + (1 * 24 * 60 * 60), // now + 1 day; 24hours; 60 minutes; 60 seconds
          ])
          ->execute();

      return $response;
    }
    else {
      throw new StatusException("The keyword request is not configured for this project.", 403);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getToken() {
    return $this->token;
  }

  /**
   * {@inheritdoc}
   */
  public function saveSelectedKeywords($keywords, $token, $contributionID) {
    // save the selected keywords if the token is correct and the timeout value is still in future
    if(!empty($keywords) && !empty($token) && !empty($contributionID)) {
      $now = time();
      $db_result = $this->dbConnection->update('dipas_keywords')
        ->fields([
          'selected_keywords' => implode(', ', $keywords),
          'contribution_id' => $contributionID,
        ])
        ->condition('token', $token, '=')
        ->condition('timeout', $now, '>=')
        ->execute();

      return $db_result;
    }
    return FALSE;
  }

}
