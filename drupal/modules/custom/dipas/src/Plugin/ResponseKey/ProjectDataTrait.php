<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\ResponseKey;

trait ProjectDataTrait {

   /**
   * Returns the active project phase.
   *
   * @return string
   *   The project phase identifier (unstarted | phase1 | phase2 | phasemix | frozen).
   */
  protected function getProjectPhase() {
    $project_start = strtotime($this->getDipasConfig()->get('ProjectSchedule.project_start'));
    $project_end = strtotime($this->getDipasConfig()->get('ProjectSchedule.project_end')) + 86399;
    $phase_2_enabled = $this->getDipasConfig()->get('ProjectSchedule.phase_2_enabled');
    $phase_2_start = $this->getDipasConfig()->get('ProjectSchedule.phase_2_start');
    $phasemix_enabled = $this->getDipasConfig()->get('ProjectSchedule.phasemix_enabled');
    $now = time();
    $project_phase = 'unstarted';

    if ($now >= $project_start && $now < $project_end) {
      $project_phase = 'phase1';

      if ($phase_2_enabled && $now >= strtotime($phase_2_start)) {
        $project_phase = $phasemix_enabled ? 'phasemix' : 'phase2';
      }
    }
    else if ($now >= $project_end) {
      $project_phase = 'frozen';
    }

    return $project_phase;
  }

  /**
   * Returns the commentStorage.
   *
   * @return \Drupal\dipas\Service\DipasConfigInterface
   */
  abstract protected function getDipasConfig();
}
