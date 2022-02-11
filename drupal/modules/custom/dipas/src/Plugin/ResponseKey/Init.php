<?php

namespace Drupal\dipas\Plugin\ResponseKey;

use Drupal\Core\Url;
use Drupal\image\Entity\ImageStyle;
use Drupal\taxonomy\TermInterface;
use Drupal\masterportal\DomainAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

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

  /**
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * {@inheritdoc}
   */
  public function setAdditionalDependencies(ContainerInterface $container) {
    $this->dateFormatter = $container->get('date.formatter');
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
  public function getPluginResponse() {
    if (!$this->isDomainDefined()) {
      $domain_settings = \Drupal::config('domain.settings');
      $redirect_url = "https://dipas.org";

      if ($domain_settings->get('redirect_url') && $domain_settings->get('redirect_url') != '') {
        $redirect_url = $domain_settings->get('redirect_url');
      }

      return ['redirect_url' => $redirect_url];
    }

    $logopath = $this->getImagePathFromMediaItemId($this->dipasConfig->get('ProjectInformation/project_logo'), 'sidebar_logo');
    $modallogo = $this->getImagePathFromMediaItemId($this->dipasConfig->get('ProjectInformation/project_logo'), 'modal_logo');
    $modalimage = $this->getImagePathFromMediaItemId($this->dipasConfig->get('ProjectInformation/project_image'), 'modal_image');

    $partnerlogos = $this->dipasConfig->get('ProjectInformation/partner_logos');
    array_walk($partnerlogos, function (&$item) {
      $item['partner_logo'] = $this->getImagePathFromMediaItemId($item['partner_logo'], 'logo_image');
    });

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
                return $item['enabled'];
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
            $this->dipasConfig->get('MenuSettings/footermenu'),
            function ($item) {
              return $item['enabled'];
            }
          )
        ),
      ],
      'taxonomy' => [
        'categories' => $this->getTermList(
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
        ),
        'rubrics_use' => (bool) $this->dipasConfig->get('ContributionSettings/rubrics_use'),
        'rubrics' => $this->getTermList('rubrics'),
        'tags' => $this->getTermList('tags'),
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
   * Extracts the wrapper uri from a file id.
   *
   * @param int $fid
   *   The file id.
   *
   * @return string
   *   The file wrapper URI.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getFileUriFromFileId($fid) {
    $file = $this->entityTypeManager->getStorage('file')->load($fid);
    return $file->get('uri')->first()->getString();
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
   * Returns a list of all terms contained in a vocabulary.
   *
   * @param string $vocab
   *   The name of the vocabulary.
   * @param array $include_fields
   *   Include the contents of the given field names in the list.
   *
   * @return array|false
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getTermList($vocab, array $include_fields = []) {
    $termlist = drupal_static('dipas_termlist', []);

    if (!isset($termlist[$vocab])) {

      // Load all terms from the given vocabulary.
      /* @var \Drupal\taxonomy\TermInterface[] $terms */
      $terms = $this->entityTypeManager->getStorage('taxonomy_term')
        ->loadByProperties([
          'vid' => $vocab,
        ]);

      /*
       * If the domain module is present, filter any terms retrieved by
       * domain assignment.
       */
      if ($this->domainModulePresent && count($terms) && $this->activeDomain !== NULL) {
        $hasDomainAccessField = reset($terms)->hasField(\Drupal\domain_access\DomainAccessManagerInterface::DOMAIN_ACCESS_FIELD);
        $hasDomainAllAccessField = reset($terms)->hasField(\Drupal\domain_access\DomainAccessManagerInterface::DOMAIN_ACCESS_ALL_FIELD);
        $terms = array_filter(
          $terms,
          function (TermInterface $term) use ($hasDomainAccessField, $hasDomainAllAccessField) {
            if ($hasDomainAccessField) {
              $assignedDomains = array_map(
                function ($assignment) {
                  return $assignment['target_id'];
                },
                $term->get(\Drupal\domain_access\DomainAccessManagerInterface::DOMAIN_ACCESS_FIELD)->getValue()
              );
            }

            $accessOnAllDomains = $hasDomainAllAccessField
              ? (bool) $term->get(\Drupal\domain_access\DomainAccessManagerInterface::DOMAIN_ACCESS_ALL_FIELD)->getString()
              : FALSE;

            if ($accessOnAllDomains || in_array($this->activeDomain->id(), $assignedDomains)) {
              return TRUE;
            }

            return FALSE;
          }
        );
      }

      // Prepare the array to only contain name and label of the term.
      $list = array_map(function ($term) {
        return ['name' => $term->label(), 'id' => $term->id()];
      }, $terms);

      // Add any fields that ought to be included into the list array.
      foreach ($include_fields as $field => $preprocess) {
        array_walk($list, function (&$termdata, $tid) use ($terms, $field, $preprocess) {
          $termdata[$field] = $preprocess($terms[$tid]->get($field)->first());
        });
      }

      // Add the preprocessed list to the term container.
      $termlist[$vocab] = $list;
    }

    return array_values($termlist[$vocab]);
  }

  /**
   * Returns a list of all files the user can download.
   *
   * List the name, the url, the mimetype and the size.
   *
   * @return array
   *   array of all media entities of type 'download' with detail information
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getDownloadPathFromEntities() {
    $entityQuery = $this->entityTypeManager->getStorage('media')->getQuery();
    $entityQuery->condition('bundle', 'download', '=');

    if ($this->isDomainModuleInstalled()) {
      $conditionGroup = $entityQuery->orConditionGroup();
      $conditionGroup->condition('field_domain_access', $this->getActiveDomain(), '=');
      $conditionGroup->condition('field_domain_all_affiliates', TRUE, '=');
      $entityQuery->condition($conditionGroup);
    }

    $media_entity_id_list = $entityQuery->execute();

    $file_urls = [];

    foreach ($media_entity_id_list as $media_entity_id => $value) {
      $media_entity = $this->entityTypeManager->getStorage('media')
        ->load($media_entity_id);

      $file_fid = $media_entity->get('field_media_file')
        ->first()
        ->get('target_id')
        ->getString();

      $file_name = $media_entity->getName();
      $file = $this->entityTypeManager->getStorage('file')->load($file_fid);

      $file_data = [
        'name' => $file_name,
        'url' => file_create_url($this->getFileUriFromFileId($file_fid)),
        'mimetype' => $file->getMimeType(),
        'size' => $file->getSize(),
      ];

      $file_urls[] = $file_data;
    }

    return $file_urls;
  }

}
