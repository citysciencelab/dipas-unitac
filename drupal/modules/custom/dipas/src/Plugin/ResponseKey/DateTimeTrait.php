<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\ResponseKey;

use \DateTime;
use \DateTimeZone;

trait DateTimeTrait {

  /**
   * Creates a DateTime object out of a given timestamp.
   *
   * @param int $timestamp
   * @param \DateTimeZone $timeZone
   *
   * @return \DateTime
   * @throws \Exception
   */
  protected function createDateTimeObject($timestamp, DateTimeZone $timeZone) {
    $dateTime = new DateTime($this->getDateFormatter()->format($timestamp, 'custom', 'm/d/Y H:i:s'), $timeZone);
    $dateTime->setTimezone(new DateTimeZone('UTC'));
    return $dateTime;
  }

  /**
   * Formats a given DateTime object into an UTC datetime string.
   *
   * @param int $timestamp
   * @param boolean $isUTC
   *
   * @return string
   * @throws \Exception
   */
  protected function convertTimestampToUTCDateTimeString($timestamp, $isUTC) {
    return $this->createDateTimeObject(
      (int) $timestamp,
      $isUTC
        ? new DateTimeZone('UTC')
        : new DateTimeZone(date_default_timezone_get())
    )->format('Y-m-d\TH:i:s\Z');
  }

  /**
   * Returns the dateFormatter service.
   *
   * @return \Drupal\Core\Datetime\DateFormatterInterface
   */
  abstract protected function getDateFormatter();

}
