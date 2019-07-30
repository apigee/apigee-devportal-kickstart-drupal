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

namespace Drupal\profile_requirement\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\profile_requirement\Plugin\ProfileRequirementManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements a controller for the profile requirement report page.
 */
class ProfileRequirementReportController implements ContainerInjectionInterface {

  /**
   * The profile requirement manager.
   *
   * @var \Drupal\profile_requirement\Plugin\ProfileRequirementManagerInterface
   */
  protected $profileRequirementManager;

  /**
   * ProfileRequirementReportController constructor.
   *
   * @param \Drupal\profile_requirement\Plugin\ProfileRequirementManagerInterface $profile_requirement_manager
   *   The profile requirement manager.
   */
  public function __construct(ProfileRequirementManagerInterface $profile_requirement_manager) {
    $this->profileRequirementManager = $profile_requirement_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.profile_requirement')
    );
  }

  /**
   * Displays the profile status report.
   *
   * @return array
   *   A render array containing a list of profile requirements.
   */
  public function status() {
    return [
      '#type' => 'profile_requirement_report_page',
      '#requirements' => $this->profileRequirementManager->listRequirements(),
    ];
  }

}
