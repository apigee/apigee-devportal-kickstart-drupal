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

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\profile_requirement\Annotation\ProfileRequirement;

/**
 * Defines a manager for profile requirement plugins.
 */
class ProfileRequirementManager extends DefaultPluginManager implements ProfileRequirementManagerInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/ProfileRequirement',
      $namespaces,
      $module_handler,
      ProfileRequirementInterface::class,
      ProfileRequirement::class
    );
    $this->alterInfo('profile_requirement_info');
    $this->setCacheBackend($cache_backend, 'profile_requirement_info_info_plugins');
  }

  /**
   * {@inheritdoc}
   */
  public function listRequirements(): array {
    $requirements = [];
    foreach ($this->getDefinitions() as $plugin_id => $definition) {
      /** @var \Drupal\profile_requirement\Plugin\ProfileRequirementInterface $instance */
      if (($instance = $this->createInstance($plugin_id)) && ($instance->isApplicable())) {
        $requirements[$plugin_id] = $instance;
      }
    }
    return $requirements;
  }

  /**
   * {@inheritdoc}
   */
  protected function findDefinitions() {
    $definitions = parent::findDefinitions();

    // Sort by weight.
    uasort($definitions, function ($a, $b) {
      return ($a['weight'] ?? 0) <=> ($b['weight'] ?? 0);
    });

    return $definitions;
  }

}
