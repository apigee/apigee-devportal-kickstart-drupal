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

/**
 * Defines a service to handle monetization tasks.
 */
class ApigeeDevportalKickstartMonetization {

  /**
   * Determines if the configured organization is monetizable.
   *
   * @return bool
   *   TRUE if organization is monetizable.
   */
  public static function isMonetizable() {
    try {
      $sdk_connector = \Drupal::service('apigee_edge.sdk_connector');
      $organization_controller = new OrganizationController($sdk_connector->getClient());
      $organization = $organization_controller->load($sdk_connector->getOrganization());
      return ($organization->getPropertyValue('features.isMonetizationEnabled') === 'true');
    }
    catch (\Exception $exception) {
      // Do not log the exception here. This litters the logs since this is run
      // before each install tasks.
    }

    return FALSE;
  }

  /**
   * Returns an array of missing dependencies.
   *
   * @return array
   *   An array of missing dependencies.
   */
  public static function getMissingDependencies(): array {
    /** @var \Drupal\Core\Extension\ExtensionList $extension_list */
    $extension_list = \Drupal::service('extension.list.module');
    $extension_list->reset();
    return array_filter(static::dependencies(), function ($dependency) use ($extension_list) {
      // If the module exists, ensure it is installed.
      // This allows users to fix missing dependencies on demand during installation.
      if ($extension_list->exists($dependency)) {
        static::ensureInstalled($dependency);
        return FALSE;
      }

      return TRUE;
    });
  }

  /**
   * Helper to ensure a module is installed.
   *
   * @param string $module
   *   The module name.
   */
  public static function ensureInstalled(string $module): void {
    try {
      \Drupal::service('module_installer')->install([$module]);
    }
    catch (\Exception $exception) {
      watchdog_exception('apigee_kickstart', $exception);
    }
  }

  /**
   * Returns an array of module names required for monetization.
   *
   * @return array
   *   An array of module names.
   */
  protected static function dependencies(): array {
    return [
      'address',
      'apigee_m10n',
      'commerce',
    ];
  }

}
