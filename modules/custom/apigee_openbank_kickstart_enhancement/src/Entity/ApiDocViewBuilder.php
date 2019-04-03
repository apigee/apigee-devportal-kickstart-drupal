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

namespace Drupal\apigee_openbank_kickstart_enhancement\Entity;

use Drupal\apigee_edge\Entity\AppViewBuilder as ApigeeEdgeAppViewBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Enhances the Apigee Edge view builder for Api Doc entity types.
 */
class ApiDocViewBuilder extends ApigeeEdgeAppViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getBuildDefaults(EntityInterface $entity, $view_mode) {
    $build = parent::getBuildDefaults($entity, $view_mode);

    // Allow ApiDoc entities to be themed.
    $build['#theme'] = $entity->getEntityTypeId();

    return $build;
  }

}
