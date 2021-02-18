<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal;

/**
 * Trait GlyphiconTrait.
 *
 * @package Drupal\masterportal
 */
trait GlyphiconTrait {

  /**
   * Make sure the string translation trait is present.
   *
   * @param string $string
   *   The string to translate.
   * @param array $args
   *   Arguments to integrate into the translated string.
   * @param array $options
   *   Options for the string translator.
   *
   * @return mixed
   *   The translated string as a translation object.
   */
  abstract protected function t($string, array $args = [], array $options = []);

  /**
   * Extract the glyphicon styles and generate an extra CSS file.
   */
  protected function generateGlyphiconStyles() {
    $initialized = &drupal_static('initializedGlyphiconStyles', FALSE);

    if (!$initialized) {
      // The path in which the CSS of the Masterportal library is located.
      $library_path = realpath(sprintf(
        '%s/%s/libraries/masterportal/css',
        DRUPAL_ROOT,
        drupal_get_path('module', 'masterportal')
      ));

      // The CSS file containing the styles for the Masterportal.
      $styleDefinitions = "{$library_path}/masterportal.css";

      // Determine when it was last changed.
      $lastchanged = filemtime($styleDefinitions);

      // Determine if there was already a style for the
      // glyphicons generated for this version.
      if (
        !file_exists("{$library_path}/style-glyphicons.css") ||
        \Drupal::state()->get('MasterportalGlyphiconStylesGenerated', 0) !== $lastchanged
      ) {

        // Load the actual style definitions.
        $css = file_get_contents($styleDefinitions);

        // Scan for the font definition containing the icons.
        preg_match('~@font\-face\s+\{.*?glyphicon.*?\}~is', $css, $font);

        // Scan for the base class definition of a glyphicon.
        preg_match('~\.glyphicon \{.+?\}~is', $css, $baseClass);

        // Scan for all Glyphicon styles contained.
        preg_match_all('~^\.glyphicon(?:\-[a-z]+?)+?\:before \{[^\}]*?\}~ism', $css, $glyphicons);

        // Merge both portions together.
        $glyphiconStyles = array_merge($font, $baseClass, $glyphicons[0]);

        // Generate the style sheet containing only the glyphicon definitions.
        file_put_contents(
          "{$library_path}/style-glyphicons.css",
          implode("\n\n", $glyphiconStyles)
        );

        // Set the variable that an actual style definition has been generated.
        \Drupal::state()->set('MasterportalGlyphiconStylesGenerated', $lastchanged);
      }

      $initialized = TRUE;
    }
  }

  /**
   * Generated an array of options containing defined glyphicons.
   *
   * @return array
   *   The options.
   */
  protected function getGlyphiconOptions() {
    $glyphicon_options = &drupal_static('glyphicon_options', FALSE);
    if ($glyphicon_options === FALSE) {
      $glyphicons = file_get_contents(realpath(sprintf(
        '%s/%s/libraries/masterportal/css/style-glyphicons.css',
        DRUPAL_ROOT,
        drupal_get_path('module', 'masterportal')
      )));
      preg_match_all('~\.(glyphicon\-(.+?))\:before\s~', $glyphicons, $matches);
      $glyphicon_options = array_combine(
        $matches[1],
        $matches[2]
      );
      asort($glyphicon_options);
    }
    return $glyphicon_options;
  }

  /**
   * Generates a form API select for the glyphicons utilizing jQuery selectmenu.
   *
   * @param string|null $default_value
   *   The default value for the select.
   * @param string $empty_option
   *   The contents of the empty option property.
   * @param array|false $states
   *   The #states array or FALSE, if not required.
   * @param string|false $description
   *   A descriptive text (if desired).
   *
   * @return array
   *   The complete form API element.
   */
  protected function getGlyphiconSelect($default_value = NULL, $empty_option = 'Please choose', $states = FALSE, $description = FALSE) {
    $select = [
      '#type' => 'select',
      '#title' => $this->t('Glyphicon', [], ['context' => 'Masterportal']),
      '#options' => $this->getGlyphiconOptions(),
      '#options_attributes' => array_combine(
        array_keys($this->getGlyphiconOptions()),
        array_map(function ($glyphiconClass) {
          return ['data-class' => $glyphiconClass];
        }, array_keys($this->getGlyphiconOptions()))
      ),
      '#empty_option' => $this->t($empty_option, [], ['context' => 'Masterportal']),
      '#default_value' => !empty($default_value) ? $default_value : NULL,
      '#attributes' => ['class' => ['selectmenu']],
      '#attached' => ['library' => ['core/jquery.ui.selectmenu']],
    ];
    if ($states !== FALSE) {
      $select['#states'] = $states;
    }
    if ($description !== FALSE) {
      $select['#description'] = $this->t($description, [], ['context' => 'Masterportal']);
    }
    return $select;
  }

}
