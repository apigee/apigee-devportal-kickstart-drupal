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

namespace Drupal\apigee_kickstart_enhancement;

use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Defines the 'apigee_kickstart.enhancer` service.
 */
interface ApigeeKickStartEnhancerInterface {

  /**
   * Returns an array of Apigee Edge App entity types.
   *
   * @return \Drupal\Core\Entity\EntityTypeInterface[]
   *   An array of Apigee Edge App entity types.
   */
  public function getAppEntityTypes(): array;

  /**
   * Determines if the given entity type is an Apigee Edge entity type.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return bool
   *   TRUE if given entity type is an Apigee Edge entity type. FALSE otherwise.
   */
  public function isAppEntityType(EntityTypeInterface $entity_type): bool;

}
