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

namespace Drupal\profile_requirement\Routing;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\profile_requirement\Plugin\ProfileRequirementManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;

/**
 * Provides routes for profile requirements.
 */
class ProfileRequirementRoutes implements ContainerInjectionInterface {

  /**
   * The profile requirement manager.
   *
   * @var \Drupal\profile_requirement\Plugin\ProfileRequirementManagerInterface
   */
  protected $profileRequirementManager;

  /**
   * ProfileRequirementRoutes constructor.
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
   * Returns an array of route objects.
   *
   * @return \Symfony\Component\Routing\Route[]
   *   An array of route objects.
   */
  public function routes() {
    $routes = [];

    foreach ($this->profileRequirementManager->listRequirements() as $requirement) {
      if ($form = $requirement->getForm()) {
        $routes["profile_requirement.{$requirement->getId()}"] = new Route(
          "/admin/reports/profile/{$requirement->getId()}",
          [
            '_form' => $form,
            '_title' => $requirement->getLabel(),
            'requirement_id' => $requirement->getId(),
          ],
          [
            '_permission' => 'administer site configuration',
          ]
        );
      }
    }

    return $routes;
  }

}
