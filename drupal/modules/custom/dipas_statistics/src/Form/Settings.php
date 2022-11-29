<?php

namespace Drupal\dipas_statistics\Form;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Settings
 *
 * @package Drupal\dipas_statistics\Form
 */
class Settings extends FormBase {

  const SETTINGS_NAME = 'dipas_statistics.settings';

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * @var \Drupal\Component\Plugin\PluginManagerInterface[]
   */
  protected $pluginManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('plugin.manager.dipas.response_key'),
      $container->get('plugin.manager.dipas.pds_response'),
      $container->get('plugin.manager.dipas.cockpitData_response')
    );
  }

  /**
   * Settings constructor
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * @param \Drupal\Component\Plugin\PluginManagerInterface $dipasResponse
   * @param \Drupal\Component\Plugin\PluginManagerInterface $pdsResponse
   * @param \Drupal\Component\Plugin\PluginManagerInterface $navigatorResponse
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    PluginManagerInterface $dipasResponse,
    PluginManagerInterface $pdsResponse,
    PluginManagerInterface $navigatorResponse
  ) {
    $this->configFactory = $config_factory;
    $this->pluginManager = [
      'DIPAS' => $dipasResponse,
      'PDS' => $pdsResponse,
      'NAVIGATOR' => $navigatorResponse,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dipas_statistics.settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->configFactory->get(self::SETTINGS_NAME);

    $form['#tree'] = TRUE;

    $form['preface'] = [
      '#type' => 'markup',
      '#markup' => sprintf(
        'Export complete tracking data: <a href="%s" target="_blank">(Export tracking data)</a>',
        Url::fromRoute('dipas_statistics.export', [])->toString()
      ),
      '#prefix' => '<p>',
      '#suffix' => '</p>',
    ];

    foreach ($this->pluginManager as $section => $pluginManager) {
      $plugins = $pluginManager->getDefinitions();

      $form[$section] = [
        '#type' => 'details',
        '#title' => $section,
        '#description' => sprintf(
          '<a href="%s" target="_blank">(Export tracking data)</a>',
          Url::fromRoute(
            'dipas_statistics.export',
            [
              'api' => $section,
            ]
          )->toString()
        ),
      ];

      $form[$section]['endpoints'] = [
        '#type' => 'checkboxes',
        '#title' => 'Select the endpoints to track',
        '#options' => array_combine(
          array_keys($plugins),
          array_map(
            function ($plugin) use ($section) {
              return sprintf(
                '%s <a href="%s" target="_blank">(Export tracking data)</a>',
                $plugin,
                Url::fromRoute(
                  'dipas_statistics.export',
                  [
                    'api' => $section,
                    'endpoint' => $plugin
                  ]
                )->toString(),
              );
            },
            array_keys($plugins)
          )
        ),
        '#default_value' => @$config->get($section) ?: [],
      ];
    }

    $form['actions'] = [
      '#type' => 'actions',

      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Save'),
        '#button_type' => 'primary',
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable(self::SETTINGS_NAME);
    $values = $form_state->getUserInput();

    foreach ($values as $api => $endpoints) {
      if (is_array($endpoints) && isset($endpoints['endpoints'])) {
        $config->set($api, array_values(array_filter($endpoints['endpoints'])));
      }
    }

    $config->save();
  }

}
