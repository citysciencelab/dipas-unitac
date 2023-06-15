<?php

namespace Drupal\dipas_statistics\Service;

use DateTimeZone;
use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DateFormatterInterface;
use \Datetime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\EncoderInterface;

class DataExport {

  protected $database;

  protected $csvEncoder;

  protected $dateFormatter;

  public function __construct(
    Connection $database,
    EncoderInterface $csv_encoder,
    DateFormatterInterface $date_formatter
  ) {
    $this->database = $database;
    $this->csvEncoder = $csv_encoder;
    $this->dateFormatter = $date_formatter;

    $this->csvEncoder->setSettings([
      'delimiter' => ';',
      'enclosure' => '"',
      'escape_char' => '\\',
      'encoding' => 'utf8',
      'strip_tags' => FALSE,
      'trim' => TRUE,
    ]);
  }

  public function export($api, $endpoint) {
    $trackingData = $this->database
                      ->select('dipas_statistics', 'ds')
                      ->fields('ds')
                      ->orderBy('ds.request_time', 'ASC');

    if ($api) {
      $trackingData->condition('ds.api', $api, '=');

      if ($endpoint) {
        $trackingData->condition('ds.endpoint', $endpoint, '=');
      }
    }

    $trackingData = $trackingData->execute()->fetchAll();

    array_walk($trackingData, function (&$row) {
      $timezone = new DateTimeZone('UTC');
      $datetime = new DateTime(
        $this->dateFormatter->format(
          $row->request_time,
          'custom',
          'm/d/Y H:i:s'
        ),
        $timezone
      );

      $row->request_time = $datetime->format('Y-m-d\TH:i:s\Z');
      $row = (array) $row;
    });

    $data = $this->csvEncoder->encode($trackingData, 'csv');
    $filename = sprintf(
      'DIPAS-TrackingData%s%s',
      $api ? "-$api" : '',
      $endpoint ? "-$endpoint" : ''
    );

    $response = new Response();
    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s.csv"', $filename));
    // Sets the BOM which helps to display special characters in Excel.
    echo chr(hexdec('EF')) . chr(hexdec('BB')) . chr(hexdec('BF'));
    $response->setContent($data);
    return $response;
  }

}
