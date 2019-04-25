<?php

/**
 * @file
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

namespace Drupal\apigee_devportal_kickstart\Installer;

use Apigee\Edge\Api\Management\Controller\OrganizationController;

class ApigeeDevportalKickstartMonetization {

  public function isMonetizable() {
    try {
      $organization_id = \Drupal::service('apigee_edge.sdk_connector')
        ->getOrganization();
      $client = \Drupal::service('apigee_edge.sdk_connector')->getClient();
      $organization_controller = new OrganizationController($client);
      $organization = $organization_controller->load($organization_id);
      return $organization->getPropertyValue('features.isMonetizationEnabled') === 'true';
    }
    catch (\Exception $exception) {
      watchdog_exception('apigee_kickstart', $exception);
    }

    return FALSE;
  }
}
