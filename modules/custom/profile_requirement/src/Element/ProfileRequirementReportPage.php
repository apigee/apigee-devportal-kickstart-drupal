<?php

/*
 * Copyright 2019 Google Inc.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 2 as published by the
 * Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public
 * License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 */

namespace Drupal\profile_requirement\Element;

use Drupal\Core\Render\Element\RenderElement;

/**
 * Creates the profile requirement report page element.
 *
 * @RenderElement("profile_requirement_report_page")
 */
class ProfileRequirementReportPage extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#theme' => 'profile_requirement_report_page',
      '#pre_render' => [
        [$class, 'preRenderRequirements'],
      ],
    ];
  }

  /**
   * Pre-render callback for requirements.
   */
  public static function preRenderRequirements($element) {
    $requirements = $element['#requirements'];
    // Group the requirement by severities.
    $element['#requirements'] = static::getSeverities();

    /** @var \Drupal\profile_requirement\Plugin\ProfileRequirementInterface $requirement */
    foreach ($requirements as $key => $requirement) {
      $element['#requirements'][$requirement->getSeverity()]['requirements'][$key] = [
        '#type' => 'profile_requirement_report',
        '#requirement' => $requirement,
      ];
    }

    return $element;
  }

  /**
   * Returns an array of severities.
   *
   * @return array
   *   An array of severities.
   */
  protected static function getSeverities() {
    return [
      'error' => [
        'title' => t('Errors'),
      ],
      'warning' => [
        'title' => t('Warnings'),
      ],
      'recommendation' => [
        'title' => t('Recommendations'),
      ],
      'completed' => [
        'title' => t('Completed'),
      ],
    ];
  }

}
