<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\EntityStorage;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Cache\MemoryCache\MemoryCacheInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\Entity\ConfigEntityStorage;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\masterportal\DomainAwareTrait;
use Drupal\masterportal\Entity\MasterportalInstanceInterface as MasterportalInstanceEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MasterportalInstance.
 *
 * Just to give our instance configuration a storage of their own. No
 * custom functionality needed here.
 *
 * @package Drupal\masterportal\EntityStorage
 */
class MasterportalInstance extends ConfigEntityStorage {
}
