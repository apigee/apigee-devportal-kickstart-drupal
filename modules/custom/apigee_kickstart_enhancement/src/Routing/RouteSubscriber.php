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

namespace Drupal\apigee_kickstart_enhancement\Routing;

use Drupal\apigee_edge\Entity\ListBuilder\DeveloperAppListBuilderForDeveloper;
use Drupal\apigee_edge_teams\Entity\ListBuilder\TeamAppListByTeam;
use Drupal\apigee_kickstart_enhancement\ApigeeKickStartEnhancerInterface;
use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Custom RouteSubscriber for Apigee Kickstart Enhancement.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * The Apigee Kickstart Enhancer.
   *
   * @var \Drupal\apigee_kickstart_enhancement\ApigeeKickStartEnhancerInterface
   */
  protected $apigeeKickstartEnhancer;

  /**
   * RouteSubscriber constructor.
   *
   * @param \Drupal\apigee_kickstart_enhancement\ApigeeKickStartEnhancerInterface $apigee_kickstart_enhancer
   *   The Apigee Kickstart Enhancer.
   */
  public function __construct(ApigeeKickStartEnhancerInterface $apigee_kickstart_enhancer) {
    $this->apigeeKickstartEnhancer = $apigee_kickstart_enhancer;
  }

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Override the controller for the Apigee Edge Apps page.
    /** @var \Drupal\Core\Entity\EntityTypeInterface $app_entity_type */
    foreach (\Drupal::service('apigee_kickstart.enhancer')->getAppEntityTypes() as $entity_type_id => $app_entity_type) {
      if ($route = $collection->get("entity.$entity_type_id.collection_by_" . str_replace('_app', '', $entity_type_id))) {
        if ($entity_type_id == 'team_app') {
          $route->setDefault('_controller', TeamAppListByTeam::class . '::render');
        }
        else {
          $route->setDefault('_controller', DeveloperAppListBuilderForDeveloper::class . '::render');
        }
      }
    }
  }

}
