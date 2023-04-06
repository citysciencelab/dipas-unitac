<?php

namespace Drupal\dipas_dev\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Http\RequestStack;
use Drupal\Core\Messenger\MessengerInterface;

class DipasDevService {

  protected $database;

  protected $currentRequest;

  protected $messenger;

  public function __construct(
    Connection $database,
    RequestStack $request_stack,
    MessengerInterface $messenger
  ) {
    $this->database = $database;
    $this->currentRequest = $request_stack->getCurrentRequest();
    $this->messenger = $messenger;
  }

  public function modifyDomainRecordsForDev() {
    $httpHost = $this->currentRequest->getHttpHost();

    if (preg_match('~localhost~i', $httpHost)) {
      $httpHost = sprintf('localhost%s', (int) $this->currentRequest->getPort() !== 80 ? ':' . $this->currentRequest->getPort() : '');
    }

    $defaultHost = $this->database->select('config')
      ->fields('config')
      ->condition('name', 'domain.record.default', '=')
      ->execute()->fetch();

    $defaultHost = unserialize($defaultHost->data);
    $defaultHost['hostname'] = $httpHost;

    $this->database->update('config')
      ->fields([
        'data' => serialize($defaultHost)
      ])
      ->condition('name', 'domain.record.default', '=')
      ->execute();

    $domainRecords = $this->database->select('config')
      ->fields('config')
      ->condition('name', 'domain.record.%', 'LIKE')
      ->condition('name', 'domain.record.default', '<>')
      ->execute()->fetchAll();

    foreach ($domainRecords as $record) {
      $data = unserialize($record->data);
      $oldHost = $data['hostname'];
      $hostname = explode('.', $oldHost);
      $subdomain = array_shift($hostname);
      $newHost = sprintf('%s.%s', $subdomain, $httpHost);
      $data['hostname'] = $newHost;

      $this->database->update('config')
        ->fields([
          'data' => serialize($data),
        ])
        ->condition('name', sprintf('domain.record.%s', $subdomain), '=')
        ->execute();
    }

    $this->messenger->addMessage('Domain-Records converted!');
  }

}
