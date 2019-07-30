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

namespace Drupal\profile_requirement\Plugin;

/**
 * Provides a trait for the profile requirement plugins.
 */
trait ProfileRequirementTrait {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The module installer service.
   *
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  protected $moduleInstaller;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The profile requirement manager.
   *
   * @var \Drupal\profile_requirement\Plugin\ProfileRequirementManagerInterface
   */
  protected $profileRequirementManager;

  /**
   * The Apigee Edge SDK connector.
   *
   * @var \Drupal\apigee_edge\SDKConnectorInterface
   */
  protected $apigeeEdgeSdkConnector;

  /**
   * Gets the entity type manager.
   *
   * @return \Drupal\Core\Entity\EntityTypeManagerInterface
   *   The entity type manager.
   */
  public function getEntityTypeManager() {
    if (!$this->entityTypeManager) {
      $this->entityTypeManager = \Drupal::entityTypeManager();
    }

    return $this->entityTypeManager;
  }

  /**
   * Gets the module handler.
   *
   * @return \Drupal\Core\Extension\ModuleHandlerInterface
   *   The module handler.
   */
  public function getModuleHandler() {
    if (!$this->moduleHandler) {
      $this->moduleHandler = \Drupal::moduleHandler();
    }

    return $this->moduleHandler;
  }

  /**
   * Gets the module installer.
   *
   * @return \Drupal\Core\Extension\ModuleInstallerInterface|mixed
   *   The module installer.
   */
  public function getModuleInstaller() {
    if (!$this->moduleInstaller) {
      $this->moduleInstaller = \Drupal::service('module_installer');
    }

    return $this->moduleInstaller;
  }

  /**
   * Gets the profile requirement manager.
   *
   * @return \Drupal\profile_requirement\Plugin\ProfileRequirementManagerInterface|mixed
   *   The profile requirement manager.
   */
  public function getProfileRequirementManager() {
    if (!$this->profileRequirementManager) {
      $this->profileRequirementManager = \Drupal::service('plugin.manager.profile_requirement');
    }

    return $this->profileRequirementManager;
  }

  /**
   * Gets the config factory.
   *
   * @return \Drupal\Core\Config\ConfigFactoryInterface
   *   The config factory.
   */
  public function getConfigFactory() {
    if (!$this->configFactory) {
      $this->configFactory = \Drupal::configFactory();
    }

    return $this->configFactory;
  }

  /**
   * Gets the Apigee Edge SDK connector.
   *
   * @return \Drupal\apigee_edge\SDKConnectorInterface|mixed
   *   The Apigee Edge SDK connector.
   */
  public function getApigeeEdgeSdkConnector() {
    if (!$this->apigeeEdgeSdkConnector) {
      $this->apigeeEdgeSdkConnector = \Drupal::service('apigee_edge.sdk_connector');
    }

    return $this->apigeeEdgeSdkConnector;
  }

}
