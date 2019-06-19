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

namespace Drupal\apigee_kickstart_enhancement\Entity\ListBuilder;

use Drupal\apigee_edge_teams\Entity\ListBuilder\TeamAppListByTeam;

/**
 * Renders the Apps list as a list of entity views instead of a table.
 */
class TeamAppListBuilder extends TeamAppListByTeam {

  /**
   * {@inheritdoc}
   */
  public function render() {
    // Render a list of apps.
    $build = $this->entityTypeManager->getViewBuilder($this->entityTypeId)->viewMultiple($this->load(), 'collapsible_card');

    // Add cache contexts.
    $build['#cache']['contexts'] = $this->entityType->getListCacheContexts();
    $build['#cache']['tags'] = $this->entityType->getListCacheTags();

    return $build;
  }

}
