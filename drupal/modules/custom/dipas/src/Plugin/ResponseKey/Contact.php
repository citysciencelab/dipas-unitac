<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\ResponseKey;

use Drupal\dipas\Annotation\ResponseKey;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Contact.
 *
 * @ResponseKey(
 *   id = "contact",
 *   description = @Translation("Returns settings for the contact page."),
 *   requestMethods = {
 *     "GET",
 *   },
 *   isCacheable = true
 * )
 *
 * @package Drupal\dipas\Plugin\ResponseKey
 */
class Contact extends ResponseKeyBase {

  use NodeContentTrait;
  use DateTimeTrait;

  /**
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * {@inheritdoc}
   */
  public function setAdditionalDependencies(ContainerInterface $container) {
    $this->dateFormatter = $container->get('date.formatter');
    $this->entityTypeManager = $container->get('entity_type.manager');
    $this->fileUrlGenerator = $container->get('file_url_generator');
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
  protected function getContentConfigPath() {
    return 'MenuSettings.footermenu.contact.node';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityTypeManager() {
    return $this->entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  protected function getFileUrlGenerator() {
    return $this->fileUrlGenerator;
  }

}
