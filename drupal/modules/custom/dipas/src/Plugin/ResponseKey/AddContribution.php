<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\ResponseKey;

use Drupal\Component\Utility\Xss;
use Drupal\dipas\Exception\MalformedRequestException;
use Drupal\dipas\Exception\StatusException;
use Drupal\dipas\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\dipas\Plugin\SettingsSection\ContributionSettings;

/**
 * Class AddContribution.
 *
 * @ResponseKey(
 *   id = "addcontribution",
 *   description = @Translation("Create a new contribution with POST data."),
 *   requestMethods = {
 *     "POST",
 *   },
 *   isCacheable = false,
 *   shieldRequest = true
 * )
 *
 * @package Drupal\dipas\Plugin\ResponseKey
 */
class AddContribution extends ResponseKeyBase {

  /**
   * @var \Drupal\dipas\Service\DipasKeywordsInterface
   */
  protected $keywordService;

  /**
   * @var \Drupal\dipas\Service\EntityServicesInterface
   */
  protected $entityServices;

  /**
   * {@inheritdoc}
   */
  protected function setAdditionalDependencies(ContainerInterface $container) {
    $this->keywordService = $container->get('dipas.keywords');
    $this->entityServices = $container->get('dipas.entity_services');
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginResponse() {
    if (
      $this->dipasConfig->get('ContributionSettings.contribution_status') === ContributionSettings::DIPAS_CONTRIBUTION_STATUS_OPEN &&
      $contributionData = json_decode($this->currentRequest->getContent())
    ) {
      list($contributionData, $missingDataKeys) = $this->getSanitizedPostData($contributionData);
      if (!empty($missingDataKeys)) {
        // Create a log entry.
        $this->logger->notice(sprintf("Tried to create a new contribution, failed to missing data bits: %s", implode(', ', $missingDataKeys)));
        // Intentionally not giving away which data bits are missing!
        throw new MalformedRequestException("Missing data!", 400);
      }

      $node = $this->transformDataIntoNode($contributionData);
      $node->save();

      // Save the selected keywords to dipas_keywords logging table
      $keywords_stored = FALSE;
      if (
        !empty($contributionData['keywords']) &&
        !empty($contributionData['token'])
      ) {
        $keywords_stored = $this->keywordService->saveSelectedKeywords($contributionData['keywords'], $contributionData['token'], $node->id());
      }

      return [
        'nid' => $node->id(),
        'keywords_stored' => $keywords_stored,
      ];
    }
    else if ($this->dipasConfig->get('ContributionSettings.contribution_status') === ContributionSettings::DIPAS_CONTRIBUTION_STATUS_CLOSED) {
      throw new StatusException("The platform is closed for new entries.", 403);
    }
    else {
      $this->logger->error(sprintf("Could not decode POST data. Original data transferred: %s", (string) $this->currentRequest->getContent()));
      throw new MalformedRequestException("Request data could not be decoded!", 400);
    }
  }

  /**
   * Sanitizes the POST data received (and checks for required fields).
   *
   * @param \stdClass $contributionData
   *   The unsanitized POST data.
   *
   * @return array
   *   An array consisting of the sanitized data and a sub-array containing missing data keys.
   */
  protected function getSanitizedPostData(\stdClass $contributionData) {
    $requiredFields = $this->getRequiredFields();
    $missingDataKeys = array_combine(
      array_keys($requiredFields),
      array_fill(0, count($requiredFields), TRUE)
    );
    foreach ($contributionData as $key => &$value) {
      // Make sure the key is lowercase.
      $key = strtolower($key);
      // XSS-sanitize ALL transferred values.
      if (is_array($value)) {
        array_walk($value, function ($elem) {
          return Xss::filter($elem);
        });
      }
      else {
        $value = Xss::filter($value);
      }
      // Cast the value to a required format (if given).
      if (isset($requiredFields[$key])) {
        settype($value, $requiredFields[$key]);
      }
      // Mark the processed data key as found.
      $missingDataKeys[$key] = FALSE;
    }
    return [(array) $contributionData, array_keys(array_filter($missingDataKeys))];
  }

  /**
   * Transforms an array containing the node data into an actual node object.
   *
   * @param array $data
   *   The data for the node.
   *
   * @return \Drupal\node\NodeInterface
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function transformDataIntoNode(array $data) {
    $data['type'] = "contribution";
    $data['status'] = 1;
    $data['field_text'] = $data['text'];

    if ($this->isSubdomain) {
      $data['field_domain_access'] = $this->activeDomain->id();
      $data['field_domain_all_affiliates'] = "0";
    }

    if ($categoryCheck = $this->checkTermAffiliation($data['category'], 'categories')) {
      $data['field_category'] = ['target_id' => $data['category']];
    }
    else {
      throw new InvalidArgumentException("The category does not exist", 409);
    }

    if (!empty($data['rubric'])) {
      if ($rubricCheck = $this->checkTermAffiliation($data['rubric'], 'rubrics')) {
        $data['field_rubric'] = [
          'target_id' => $data['rubric'],
        ];
      }
      else {
        throw new InvalidArgumentException("The rubric does not exist", 409);
      }
    }

    if (!empty($data['geodata']) && ($geodata = json_decode($data['geodata'])) && !empty($geodata->geometry) && !empty($geodata->properties->centerPoint)) {
      if (!count((array) $geodata->properties->centerPoint->coordinates)) {
        // centerPoint Coordinate empty due to masterportal problems, calculate again here as temporary fix (17.12.2020, CB)
        switch ($geodata->geometry->type) {
          case 'Point':
            $geodata->properties->centerPoint->coordinates = $geodata->geometry->coordinates;
            break;
          case 'LineString':
            $geodata->properties->centerPoint->coordinates = $this->calculateLineStringMidPoint($geodata->geometry->coordinates);
            break;
          case 'Polygon':
            $geodata->properties->centerPoint->coordinates = $this->calculatePolygonMidPoint($geodata->geometry->coordinates);
            break;
        }
      }

      $fieldValue = [
        'geometry' => $geodata->geometry,
        'centerPoint' => $geodata->properties->centerPoint,
      ];
      $data['field_geodata'] = [
        'value' => json_encode($fieldValue),
      ];
    }

    if (!empty($data['user']) && $user = $this->entityTypeManager->getStorage('user')->load($data['user'])) {
      $data['uid'] = $user->id();
    }

    if (!empty($data['keywords']) && !empty($data['token'])) {
      $data['field_tags'] = $this->entityServices->transformStringsIntoTaxonomyTerms('tags', $data['keywords'], TRUE);
    }

    return $this->entityTypeManager->getStorage('node')->create($data);
  }

  /**
   * Returns the field names that are required.
   *
   * @return string[]
   */
  protected function getRequiredFields() {
    $required_fields = [
      'title' => 'string',
      'text' => 'string',
      'category' => 'int',
    ];

    if ($this->dipasConfig->get('ContributionSettings.rubrics_use')) {
      $required_fields += [
        'rubric' => 'int',
      ];
    }
    if ($this->dipasConfig->get('ContributionSettings.contributions_must_be_localized')) {
      $required_fields += [
        'geodata' => 'string',
      ];
    }
    if ($this->dipasConfig->get('ContributionSettings.contributor_must_be_logged_in')) {
      $required_fields += [
        'user' => 'int',
      ];
    }
    return $required_fields;
  }

  /**
   * {@inheritdoc}
   */
  protected function getResponseKeyCacheTags() {
    return [];
  }

  /**
   * Returns if ID of term is contained in a vocabulary.
   *
   * @param array $tid
   *   The ID of the term to be checked
   *
   * @param string $vocab
   *   The name of the vocabulary.
   *
   * @return boolean
   */
  protected function checkTermAffiliation($tid, $vocab) {
    if ($term = $this->entityTypeManager->getStorage('taxonomy_term')->load($tid)) {
      return $term->bundle() === $vocab ? TRUE : FALSE;
    }

    return FALSE;
  }

  /**
   * Calculate LineString middle point to save as center coordinate in geometry
   *
   * @param array $geometry
   *    The line string geometry used to calculate the mid point
   *
   * @return array
   */
  protected function calculateLineStringMidPoint($geometry) {
    $numberPoints = count($geometry);

    if ($numberPoints % 2 !== 0) {
      $index = ($numberPoints - 1) / 2;
      return $geometry[$index];
    }
    else {
      $index = $numberPoints / 2;
      $coordBeforeMid = $geometry[$index - 1];
      $coordAfterMid = $geometry[$index];

      $xMidDelta = abs($coordAfterMid[0] - $coordBeforeMid[0]) / 2;
      $yMidDelta = abs($coordAfterMid[1] - $coordBeforeMid[1]) / 2;

      $xMid = min($coordBeforeMid[0], $coordAfterMid[0]) + $xMidDelta;
      $yMid = min($coordBeforeMid[1], $coordAfterMid[1]) + $yMidDelta;

      return [$xMid, $yMid];
    }
  }

  /**
   * Calculate Polygon middle point to save as center coordinate in geometry
   *
   * @param array $geometry
   *    The polygon geometry used to calculate the mid point
   *
   * @return array
   */
  protected function calculatePolygonMidPoint($geometry) {
    $latall = array();
    $lonall = array();

    foreach($geometry[0] as $geom) {
      array_push($latall, $geom[1]);
      array_push($lonall, $geom[0]);
    }

    $minlon = min($lonall);
    $maxlon = max($lonall);
    $minlat = min($latall);
    $maxlat = max($latall);

    $midlon = $minlon + (($maxlon - $minlon ) / 2);
    $midlat = $minlat + (($maxlat - $minlat ) / 2);

    return [$midlon, $midlat];
  }

}
