<?php

/**
 * Copyright 2018 Google Inc.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * version 2 as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 */

namespace Drupal\apigee_openbank_kickstart_enhancement\Entity\ListBuilder;

use Drupal\apigee_edge\Entity\ListBuilder\DeveloperAppListBuilderForDeveloper;

/**
 * Renders the Apps list as a list of entity views instead of a table.
 */
class AppListBuilder extends DeveloperAppListBuilderForDeveloper {

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = [];
    $view_builder = $this->entityTypeManager->getViewBuilder($this->entityTypeId);

    // Render a list of apps.
    // We cannot do ViewBuilder::viewMultiple here because \Drupal\apigee_edge\Entity\AppViewBuilder::buildMultiple
    // assumes $build_list is an array of render arrays whereas $build_list may
    // contain non-renderaable elements. Example: #sorted or #pre_render.
    foreach ($this->load() as $entity) {
      $build[] = $view_builder->view($entity, 'collapsible_card');
    }

    return $build;
  }

}
