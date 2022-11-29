<?php

namespace Drupal\dipas\Plugin\ResponseKey;

use Drupal\Core\Url;
use Drupal\image\Entity\ImageStyle;
use Drupal\masterportal\DomainAwareTrait;
use Drupal\dipas\FileHelperFunctionsTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\dipas\TaxonomyTermFunctionsTrait;
use Drupal\dipas\ProceedingListingMethodsTrait;

/**
 * Class Init.
 *
 * @ResponseKey(
 *   id = "init",
 *   description = @Translation("Combines all basic project information into a
 *   single response."), requestMethods = {
 *     "GET",
 *   },
 *   isCacheable = true
 * )
 *
 * @package Drupal\dipas\Plugin\ResponseKey
 */
class Init extends ResponseKeyBase {

  use DateTimeTrait;
  use ProjectDataTrait;
  use DomainAwareTrait;
  use TaxonomyTermFunctionsTrait;
  use FileHelperFunctionsTrait;
  use ProceedingListingMethodsTrait;

  /**
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Drupals taxonomy term storage service.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $termStorage;

  /**
   * {@inheritdoc}
   */
  public function setAdditionalDependencies(ContainerInterface $container) {
    $this->dateFormatter = $container->get('date.formatter');
    $this->termStorage = $this->entityTypeManager->getStorage('taxonomy_term');
  }

  /**
   * {@inheritdoc}
   */
  protected function getDateFormatter() {
    return $this->dateFormatter;
  }

  /**
   * {@inheritdoc}
   */
  protected function getDipasConfig() {
    return $this->dipasConfig;
  }

  /**
   * {@inheritdoc}
   */
  protected function getConfig($domainid)
  {
    return $this->dipasConfig;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginResponse() {
    if (!$this->isDomainDefined()) {
      $domain_settings = \Drupal::config('domain.settings');
      $redirect_url = "https://www.hamburg.de/stadtwerkstatt";

      if ($domain_settings->get('redirect_url') && $domain_settings->get('redirect_url') != '') {
        $redirect_url = $domain_settings->get('redirect_url');
      }

      return ['redirect_url' => $redirect_url];
    }

    $logopath = $this->getImagePathFromMediaItemId($this->dipasConfig->get('ProjectInformation/project_logo'), 'sidebar_logo');
    $modallogo = $this->getImagePathFromMediaItemId($this->dipasConfig->get('ProjectInformation/project_logo'), 'modal_logo');
    $modalimage = $this->getImagePathFromMediaItemId($this->dipasConfig->get('ProjectInformation/project_image'), 'modal_image');

    $partnerlogos = $this->dipasConfig->get('ProjectInformation/partner_logos');

    if (is_array($partnerlogos)) {
      array_walk($partnerlogos, function (&$item) {
        $item['partner_logo_alttext'] = $this->getAlternativeTextForMediaItem($item['partner_logo']);
        $item['partner_logo'] = $this->getImagePathFromMediaItemId($item['partner_logo'], 'logo_image');
      });
    }

    $downloadData = $this->getDownloadPathFromEntities();

    $settings = [
      'maintenanceMode' => FALSE,
      'projectphase' => $this->getProjectPhase(),
      'enabledPhase2' => $this->dipasConfig->get('ProjectSchedule/phase_2_enabled'),
      'projectperiod' => [
        'start' => $this->convertTimestampToUTCDateTimeString(
          strtotime($this->dipasConfig->get('ProjectSchedule/project_start')),
          TRUE
        ),
        'end' => $this->convertTimestampToUTCDateTimeString(
          strtotime($this->dipasConfig->get('ProjectSchedule/project_end')),
          TRUE
        ),
      ],
      'conception_comments_state' => (bool) $this->dipasConfig->get('ProjectSchedule/allow_conception_comments') ? 'open' : 'closed',
      'display_existing_conception_comments' => (bool) $this->dipasConfig->get('ProjectSchedule/display_existing_conception_comments'),
      'projecttitle' => $this->dipasConfig->get('ProjectInformation/site_name'),
      'projectlogo' => [
        'path' => $logopath,
        'alttext' => $this->getAlternativeTextForMediaItem($this->dipasConfig->get('ProjectInformation/project_logo')),
      ],
      'modallogo' => [
        'path' => $modallogo,
        'alttext' => $this->getAlternativeTextForMediaItem($this->dipasConfig->get('ProjectInformation/project_logo')),
      ],
      'projectowner' => [
        'name' => $this->dipasConfig->get('ProjectInformation/department'),
        'street1' => $this->dipasConfig->get('ProjectInformation/street1'),
        'street2' => $this->dipasConfig->get('ProjectInformation/street2'),
        'zip' => $this->dipasConfig->get('ProjectInformation/zip'),
        'city' => $this->dipasConfig->get('ProjectInformation/city'),
        'telephone' => $this->dipasConfig->get('ProjectInformation/contact_telephone'),
        'email' => $this->dipasConfig->get('ProjectInformation/contact_email'),
        'website' => $this->dipasConfig->get('ProjectInformation/contact_website'),
      ],
      'welcomemodal' => [
        'headline' => $this->dipasConfig->get('ProjectInformation/headline'),
        'text' => $this->dipasConfig->get('ProjectInformation/text'),
        'image' => [
          'path' => $modalimage,
          'alttext' => $this->getAlternativeTextForMediaItem($this->dipasConfig->get('ProjectInformation/project_image')),
        ],
      ],
      'partnerlogos' => $partnerlogos,
      'menus' => [
        'main' => array_map(
          function ($item) {
            $menuitem = [
              'name' => $item['name'],
              'icon' => $item['icon'],
            ];
            if (isset($item['url'])) {
              $menuitem['url'] = $item['url'];
            }
            return $menuitem;
          },
          array_merge(
            array_filter(
              $this->dipasConfig->get('MenuSettings/mainmenu'),
              function ($item) {
                if (isset($item['overwriteFrontpage'])) {
                  if (!($this->getProjectPhase() === 'phase2' || $this->getProjectPhase() === 'phasemix' || ($this->getProjectPhase() === 'frozen' && $this->dipasConfig->get('ProjectSchedule/phase_2_enabled')))) {
                    return;
                  }
                }
                return $item['enabled'] ?: FALSE;
              }
            ),
            array_filter(
              [
                'conceptionlist' => [
                  'name' => $this->dipasConfig->get('MenuSettings/mainmenu/conceptionlist/name'),
                  'icon' => 'compare_arrows',
                ],
              ],
              function () {
                return $this->getProjectPhase() === 'phase2' || $this->getProjectPhase() === 'phasemix' || ($this->getProjectPhase() === 'frozen' && $this->dipasConfig->get('ProjectSchedule/phase_2_enabled'));
              }
            )
          )
        ),
        'footer' => array_map(
          function ($item) {
            return $item['name'];
          },
          array_filter(
            $this->dipasConfig->get('MenuSettings/footermenu') ?: [],
            function ($item) {
              return $item['enabled'];
            }
          )
        ),
      ],
      'taxonomy' => [
        'categories' => array_values($this->getTermList(
          'categories',
          [
            'field_category_icon' => function ($fieldvalue) {
              return preg_replace('~^https?:~i', '', $this->createImageUrl($this->getFileUriFromFileId($fieldvalue->get('target_id')
                ->getString())));
            },
            'field_color' => function ($fieldvalue) {
              return $fieldvalue->getString();
            },
          ]
        )),
        'rubrics_use' => (bool) $this->dipasConfig->get('ContributionSettings/rubrics_use'),
        'rubrics' => array_values($this->getTermList('rubrics')),
        'tags' => array_values($this->getTermList('tags')),
      ],
      'image_styles' => $this->getContentImageStyleList(),
      'contributions' => [
        'status' => $this->dipasConfig->get('ContributionSettings/contribution_status'),
        'maxlength' => $this->dipasConfig->get('ContributionSettings/maximum_character_count_per_contribution'),
        'geometry' => $this->dipasConfig->get('ContributionSettings/geometry'),
        'comments' => [
          'form' => $this->dipasConfig->get('ContributionSettings/comments_allowed') ? 'open' : 'closed',
          'maxlength' => $this->dipasConfig->get('ContributionSettings/comments_maxlength'),
          'display' => $this->dipasConfig->get('ContributionSettings/display_existing_comments'),
        ],
        'ratings' => $this->dipasConfig->get('ContributionSettings/rating_allowed'),
      ],
      'masterportal_instances' => [
        'contributionmap' => preg_replace('~^https?:~i', '', Url::fromRoute('masterportal.fullscreen', ['masterportal_instance' => $this->dipasConfig->get('ContributionSettings/masterportal_instances/contributionmap')], ['absolute' => TRUE])
          ->toString()),
        'singlecontribution' => [
          'url' => preg_replace('~^https?:~i', '', Url::fromRoute('masterportal.fullscreen', ['masterportal_instance' => $this->dipasConfig->get('ContributionSettings/masterportal_instances/singlecontribution/instance')], ['absolute' => TRUE])
            ->toString()),
          'other_contributions' => $this->dipasConfig->get('ContributionSettings/masterportal_instances/singlecontribution/other_contributions'),
        ],
        'createcontribution' => [
          'url' => preg_replace('~^https?:~i', '', Url::fromRoute('masterportal.fullscreen', ['masterportal_instance' => $this->dipasConfig->get('ContributionSettings/masterportal_instances/createcontribution')], ['absolute' => TRUE])
            ->toString()),
          'must_be_localized' => (bool) $this->dipasConfig->get('ContributionSettings/contributions_must_be_localized'),
        ],
        'schedule' => preg_replace('~^https?:~i', '', Url::fromRoute('masterportal.fullscreen', ['masterportal_instance' => $this->dipasConfig->get('MenuSettings/mainmenu/schedule/mapinstance')], ['absolute' => TRUE])
          ->toString()),
      ],
      'downloads' => $downloadData,
      'sidebar' => $this->dipasConfig->get('SidebarSettings/blocks'),
      'keyword_service_enabled' => $this->dipasConfig->get('KeywordSettings/enabled'),
      'frontpage' => $this->dipasConfig->get('MenuSettings/mainmenu/conceptionlist/overwriteFrontpage') && ($this->getProjectPhase() === 'phase2' || $this->getProjectPhase() === 'phasemix' || ($this->getProjectPhase() === 'frozen' && $this->dipasConfig->get('ProjectSchedule/phase_2_enabled'))) ? 'conceptionlist' : $this->dipasConfig->get('MenuSettings/frontpage'),
    ];

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public static function postProcessResponse(array $responsedata) {
    [$cypher, $security_token, $passphrase, $initvector] = require_once(realpath(__DIR__ . '/RestApiToken.php'));

    $responsedata['checksum'] = openssl_encrypt($security_token, $cypher, $passphrase, 0, $initvector);
    $responsedata['signature'] = $initvector;
    $responsedata['timestamp'] = time();

    return $responsedata;
  }

  /**
   * {@inheritdoc}
   */
  protected function getResponseKeyCacheTags() {
    return [];
  }

  /**
   * Helper function to retrieve the ALT text from a media entity.
   *
   * @param int $id
   *   The ID of the media entity.
   *
   * @return string
   *   The alternative text
   */
  protected function getAlternativeTextForMediaItem($id) {
    if (
      !is_null($id) &&
      is_numeric($id) &&
      $media_entity = $this->entityTypeManager
        ->getStorage('media')
        ->load($id)
    ) {
      return $media_entity->get('field_media_image')
        ->first()
        ->get('alt')
        ->getString();
    }
    return '';
  }

  /**
   * Returns the image path from a media item entity.
   *
   * @param int $id
   *   The ID of the media entity.
   * @param string $image_style
   *   The image style to use for the path (optional).
   *
   * @return string
   *   The path to the image stored in the media item.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *   Exceptions thrown by the entity type manager.
   */
  protected function getImagePathFromMediaItemId($id, $image_style = FALSE) {
    if (
      !is_null($id) &&
      is_numeric($id) &&
      $media_entity = $this->entityTypeManager
        ->getStorage('media')
        ->load($id)
    ) {
      $image_fid = $media_entity->get('field_media_image')
        ->first()
        ->get('target_id')
        ->getString();
      return preg_replace('~^https?:~i', '', $this->createImageUrl($this->getFileUriFromFileId($image_fid), $image_style));
    }
    return '';
  }

  /**
   * Creates a fully qualified image url from a wrapper uri.
   *
   * @param string $wrapperUri
   *   The image wrapper uri.
   * @param bool $image_style
   *   Optional image style to be used.
   *
   * @return string
   *   The fully qualified image url.
   */
  protected function createImageUrl($wrapperUri, $image_style = FALSE) {
    return $image_style !== FALSE
      ? ImageStyle::load($image_style)->buildUrl($wrapperUri)
      : file_create_url($wrapperUri);
  }

  /**
   * {@inheritdoc}
   */
  protected function getTermStorage() {
    return $this->termStorage;
  }
}
