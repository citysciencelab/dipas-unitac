<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\ResponseKey;

trait RetrieveRatingTrait {

  /**
   * @return array
   */
  protected function getRating() {
    $ratingdata = [
      'allVotes' => 0,
      'upVotes' => 0,
      'downVotes' => 0,
      'voteAverage' => 0,
      'lastVote' => FALSE,
    ];

    $query = $this->getDatabase()->select('votingapi_vote', 'ratings')
      ->condition('ratings.type', 'vote', '=')
      ->condition('ratings.entity_type', $this->getEntityTypeId(), '=')
      ->condition('ratings.entity_id', $this->getEntityId(), '=')
      ->groupBy('ratings.entity_type, ratings.entity_id');

    $query->addExpression('COUNT(ratings.id)', 'allVotes');
    $query->addExpression('SUM(CASE ratings.value WHEN 1 THEN 1 ELSE 0 END)', 'upVotes');
    $query->addExpression('SUM(CASE ratings.value WHEN -1 THEN 1 ELSE 0 END)', 'downVotes');
    $query->addExpression('AVG(ratings.value)', 'voteAverage');
    $query->addExpression('MAX(ratings.timestamp)', 'lastVote');

    $result = $query->execute()->fetchAll();

    if (!empty($result)) {
      foreach (array_keys($ratingdata) as $key) {
        $ratingdata[$key] = $result[0]->{$key};
      }
      $ratingdata['lastVote'] = $this->convertTimestampToUTCDateTimeString((int) $ratingdata['lastVote'], FALSE);
    }

    return $ratingdata;
  }

  /**
   * Returns the database connection to use.
   *
   * @return \Drupal\Core\Database\Connection
   */
  abstract protected function getDatabase();

  /**
   * Returns the entity type id to fetch rating results for.
   *
   * @return string
   */
  abstract protected function getEntityTypeId();

  /**
   * Returns the entity id to fetch rating results for.
   *
   * @return int
   */
  abstract protected function getEntityId();

  /**
   * Formats a given DateTime object into an UTC datetime string.
   *
   * @param int $timestamp
   * @param boolean $isUTC
   *
   * @return string
   * @throws \Exception
   */
  abstract protected function convertTimestampToUTCDateTimeString($timestamp, $isUTC);

}
