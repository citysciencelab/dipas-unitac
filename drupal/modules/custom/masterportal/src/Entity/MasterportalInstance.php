<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\Annotation\ConfigEntityType;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\masterportal\Plugin\Masterportal\InstanceConfigSection\LayerSectionPluginInterface;

/**
 * Class MasterportalInstance.
 *
 * Custom configuration entity type that contains all instance-specific settings
 * for a Masterportal integration.
 *
 * @package Drupal\masterportal\Entity
 *
 * @ConfigEntityType(
 *   id = "masterportal_instance",
 *   label = @Translation("Masterportal instance"),
 *   module = "masterportal",
 *   config_prefix = "instance",
 *   admin_permission = "access masterportal configuration ui",
 *   translatable = FALSE,
 *   handlers = {
 *     "storage" = "Drupal\masterportal\EntityStorage\MasterportalInstance",
 *     "list_builder" = "Drupal\masterportal\ListBuilder\MasterportalInstances",
 *     "form" = {
 *       "default" = "Drupal\masterportal\Form\MasterportalInstanceEditForm",
 *       "delete" = "Drupal\masterportal\Form\MasterportalInstanceDeleteForm"
 *     },
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/user-interface/masterportal/instances/{masterportal_instance}",
 *     "delete-form" = "/admin/config/user-interface/masterportal/instances/{masterportal_instance}/delete"
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "uid" = "uid",
 *     "instance_name" = "instance_name",
 *     "label" = "label",
 *     "domain" = "domain",
 *     "hidden" = "hidden",
 *   },
 *   config_export = {
 *     "id" = "id",
 *     "uid" = "uid",
 *     "instance_name" = "instance_name",
 *     "label" = "label",
 *     "domain" = "domain",
 *     "hidden" = "hidden",
 *     "settings" = "settings"
 *   }
 * )
 */
class MasterportalInstance extends ConfigEntityBase implements MasterportalInstanceInterface {

  use StringTranslationTrait;

  /**
   * The machine name of this configuration.
   *
   * Consists of the domain key (if domain module isn't installed 'default'),
   * and the instance name.
   *
   * @var string
   */
  protected $id;

  /**
   * The owner user id of this instance.
   *
   * @var int
   */
  protected $uid;

  /**
   * The instance name.
   *
   * Machine readable version without the domain prefix.
   *
   * @var string
   */
  protected $instance_name;

  /**
   * The human readable name of this configuration.
   *
   * @var string
   */
  protected $label;

  /**
   * The domain of this instance if the domain module is used.
   *
   * @var string
   */
  protected $domain;

  /**
   * Should the current entity get listed on the instances page?
   *
   * @var bool
   */
  protected $hidden;

  /**
   * The configuration settings for this instance.
   *
   * @var array
   */
  protected $settings;

  /**
   * Custom plugin manager for configuration section plugins.
   *
   * @var \Drupal\masterportal\PluginSystem\PluginManagerInterface
   */
  protected $configurationSectionManager;

  /**
   * The current (possibly logged-in) user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The configuration service for basic Masterportal configurations.
   *
   * @var \Drupal\masterportal\Service\MasterportalConfigInterface
   */
  protected $masterportalConfigService;

  /**
   * MasterportalInstance constructor.
   *
   * @param array $values
   *   An array of values to set, keyed by property name. If the entity type
   *   has bundles, the bundle key has to be specified.
   * @param string $entity_type
   *   The type of the entity to create.
   */
  public function __construct(array $values, $entity_type) {
    parent::__construct($values, $entity_type);
    // No nice dependency injection here.
    $container = \Drupal::getContainer();
    $this->configurationSectionManager = $container->get('plugin.manager.masterportal.instance_config_section');
    $this->currentUser = $container->get('current_user');
    $this->masterportalConfigService = $container->get('masterportal.config');
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    // If the isn't set yet we assume that the entity is new and hasn't a valid id.
    return empty($this->getName()) ? NULL : $this->getDomain() . '.' . $this->getName();
  }

  /**
   * {@inheritdoc}
   */
  public static function persistentInstances() {
    return [
      'config',
      'default',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getUiStyleLabel($uiStyle = FALSE) {
    $uiStyles = [
      'default' => $this->t('Default', [], ['context' => 'Masterportal']),
      'table' => $this->t('Touch table', [], ['context' => 'Masterportal']),
      'simple' => $this->t('No menu bar', [], ['context' => 'Masterportal']),
    ];
    return $uiStyle === FALSE ? $uiStyles : $uiStyles[$uiStyle];
  }

  /**
   * {@inheritdoc}
   */
  public function getAllLayerIdsInUse() {
    // Prepare a container for the results.
    $layerIdsInUse = [];

    // Get the configured settings.
    $settings = $this->get('settings');

    // Iterate over the configuration.
    foreach ($settings as $pluginId => $configuration) {

      // Get an instance of the respective configSectionPlugin.
      $pluginDefinition = $this->configurationSectionManager->getPluginDefinitions($pluginId);
      $plugin = new $pluginDefinition['class']($configuration, $this->masterportalConfigService);

      // If the current configuration section deals with layers,
      // get the layer ids in use.
      if ($plugin instanceof LayerSectionPluginInterface) {
        $layerIdsInUse = array_merge($layerIdsInUse, $plugin->getLayerIdsInUse());
      }

    }

    // Return the used layer ids.
    return array_unique($layerIdsInUse);
  }

  /**
   * {@inheritdoc}
   */
  public function save() {
    if ($this->isNew()) {
      $this->uid = $this->currentUser->id();
    }
    return parent::save();
  }

  /**
   * {@inheritDoc}
   */
  public function getDomain() {
    return isset($this->domain) ? $this->domain : 'default';
  }

  /**
   * {@inheritDoc}
   */
  public function setDomain($domain) {
    $this->domain;
  }

  /**
   * {@inheritDoc}
   */
  public function getName() {
    return $this->instance_name;
  }

}
