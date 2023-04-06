<?php

namespace Drupal\dipas;

trait ProceedingListingMethodsTrait {

  /**
   * Getter function for AND toggle function to set the node listing domain sensitivity.
   *
   * @param bool $isSensitive
   *   Set the listing to be domain sensitive or not.
   *
   * @return bool
   *   The domain sensitivity flag.
   */
  protected function listingIsDomainSensitive($isSensitive = NULL) {
    static $listingIsDomainsensitive;

    if ($listingIsDomainsensitive === NULL || $isSensitive !== NULL) {
      if ($this->isDomainModuleInstalled()) {
        if ($listingIsDomainsensitive === NULL && $isSensitive === NULL) {
          $listingIsDomainsensitive = TRUE;
        }
        else {
          $listingIsDomainsensitive = $isSensitive;
        }
      }
      else {
        $listingIsDomainsensitive = FALSE;
      }
    }

    return $listingIsDomainsensitive;
  }

  /**
   * Returns a list of available proceeding IDs.
   *
   * @param string $include
   *   Determines the type of proceedings that should get included (all|visible|active).
   *
   * @return string[]
   *   List of proceeding IDs.
   */
  protected function getProceedingIDs($include = 'all') {
    $proceedingids = drupal_static(
      'dipas_proceedingids',
      [
        'all' => null,
        'active' => null,
        'visible' => null,
      ]
    );

    $include = strtolower($include);
    if (!in_array($include, ["all", "visible", "active"])) {
      $include = "all";
    }

    if ($proceedingids['all'] === null) {
      $now = time();

      if (!$this->isDomainModuleInstalled()) {
        $proceedingids['all'] = ['default'];
        $proceedingids['visible'] = [];
        $proceedingids['active'] = [];

        $defaultConfig = $this->getConfig('dipas.default.configuration');
        $start = strtotime($defaultConfig->get('ProjectSchedule.project_start'));
        $end = strtotime($defaultConfig->get('ProjectSchedule.project_end'));

        if (!$defaultConfig->get('Export.proceeding_is_internal')) {
          $proceedingids['visible'][] = 'default';
        }

        if ($start <= $now && $now < $end) {
          $proceedingids['active'][] = 'default';
        }
      }
      else {
        $proceedingids['all'] = [];
        $proceedingids['active'] = [];
        $proceedingids['visible'] = [];

        $result = $this->getDatabase()->select('config', 'c')
          ->fields('c', ['name'])
          ->condition('c.name', 'dipas.%.configuration', 'LIKE')
          ->execute()
          ->fetchAll();

        foreach ($result as $configid) {
          [, $proceedingid,] = explode('.', $configid->name);

          if ($proceedingid === 'default') {
            continue;
          }

          $proceedingids['all'][] = $proceedingid;

          $proceedingConfig = $this->getConfig($proceedingid);

          if (!$proceedingConfig->get('Export.proceeding_is_internal')) {
            $proceedingids['visible'][] = $proceedingid;
          }

          $start = strtotime($proceedingConfig->get('ProjectSchedule.project_start'));
          $end = strtotime($proceedingConfig->get('ProjectSchedule.project_end'));

          if ($start && $end && $start <= $now && $now < $end) {
            $proceedingids['active'][] = $proceedingid;
          }
        }
      }
    }

    return $proceedingids[$include];
  }

  abstract protected function getDatabase();

  /**
   * @return \Drupal\dipas\Service\DipasConfigInterface
   */
  abstract protected function getDipasConfig();

  abstract protected function getConfig($domainid);

  abstract protected function isDomainModuleInstalled();

}
