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

namespace Drupal\apigee_devportal_kickstart;

use Apigee\Edge\Api\Management\Controller\OrganizationController;
use Drupal\apigee_edge\SDKConnectorInterface;

class ApigeeDevportalKickstartManager {

  /**
   * {@inheritdoc}
   */
  public static function isMonetizationEnabled(): bool {
    global $install_state;
    if (isset($install_state['monetization_enabled'])) {
      return $install_state['monetization_enabled'];
    }

    /** @var SDKConnectorInterface $sdk_connector */
    /** @var \Drupal\Core\Cache\CacheBackendInterface $cache */
    if (($sdk_connector = \Drupal::service('apigee_edge.sdk_connector')) && ($cache = \Drupal::service('cache.default'))) {
      // Get organization ID string.
      $org_id = $sdk_connector->getOrganization();

      // Use cached result if available.
      $monetization_status_cache_entry = $cache->get("apigee_m10n:org_monetization_status:{$org_id}");
      $monetization_status = $monetization_status_cache_entry ? $monetization_status_cache_entry->data : NULL;

      if (!$monetization_status) {
        // Load organization and populate cache.
        $org_controller = new OrganizationController($sdk_connector->getClient());

        try {
          /** @var \Apigee\Edge\Api\Management\Entity\OrganizationInterface $org */
          $org = $org_controller->load($org_id);
          $monetization_status = $org->getPropertyValue('features.isMonetizationEnabled') === 'true' ? 'enabled' : 'disabled';

          $expire_time = new \DateTime('now + 5 minutes');
          $cache->set("apigee_m10n:org_monetization_status:{$org_id}", $monetization_status, $expire_time->getTimestamp());
        } catch (\Exception $e) {
          return FALSE;
        }
      }

      return ($monetization_status === 'enabled');
    }

    return FALSE;
  }
}
