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

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Menu\LocalTaskManager;
use Drupal\Core\Url;

class ApigeeLocalTaskManager extends LocalTaskManager {


  public function getTaskForUrl(Url $url, $level = 0) {
    $route_name = $url->getRouteName();
    if (!isset($this->taskData[$route_name])) {
      $cacheability = new CacheableMetadata();
      $cacheability->addCacheContexts(['route']);
      // Look for route-based tabs.
      $this->taskData[$route_name] = [
        'tabs' => [],
        'cacheability' => $cacheability,
      ];

      if (!$this->requestStack->getCurrentRequest()->attributes->has('exception')) {
        // Safe to build tasks only when no exceptions raised.
        $data = [];
        $local_tasks = $this->getTasksBuild($route_name, $cacheability);
        foreach ($local_tasks as $tab_level => $items) {
          $data[$tab_level] = empty($data[$tab_level]) ? $items : array_merge($data[$tab_level], $items);
        }
        $this->taskData[$route_name]['tabs'] = $data;
        // Allow modules to alter local tasks.
        $this->moduleHandler->alter('menu_local_tasks', $this->taskData[$route_name], $route_name, $cacheability);
        $this->taskData[$route_name]['cacheability'] = $cacheability;
      }
    }

    if (isset($this->taskData[$route_name]['tabs'][$level])) {
      return [
        'tabs' => $this->taskData[$route_name]['tabs'][$level],
        'route_name' => $route_name,
        'cacheability' => $this->taskData[$route_name]['cacheability'],
      ];
    }

    return [
      'tabs' => [],
      'route_name' => $route_name,
      'cacheability' => $this->taskData[$route_name]['cacheability'],
    ];
  }

  public function getTasksBuildForUrl(Url $url, RefinableCacheableDependencyInterface &$cacheability) {
    $tree = $this->getLocalTasksForRoute($url->getRouteName());
    $build = [];

    // Collect all route names.
    $route_names = [];
    foreach ($tree as $instances) {
      foreach ($instances as $child) {
        $route_names[] = $child->getRouteName();
      }
    }
    // Pre-fetch all routes involved in the tree. This reduces the number
    // of SQL queries that would otherwise be triggered by the access manager.
    if ($route_names) {
      $this->routeProvider->getRoutesByNames($route_names);
    }

    foreach ($tree as $level => $instances) {
      /** @var $instances \Drupal\Core\Menu\LocalTaskInterface[] */
      foreach ($instances as $plugin_id => $child) {
        $route_name = $child->getRouteName();
        $route_parameters = $url->getRouteParameters();

        // Given that the active flag depends on the route we have to add the
        // route cache context.
        $cacheability->addCacheContexts(['route']);
        $active = $this->isRouteActive($url->getRouteName(), $route_name, $route_parameters);

        // The plugin may have been set active in getLocalTasksForRoute() if
        // one of its child tabs is the active tab.
        $active = $active || $child->getActive();
        // @todo It might make sense to use link render elements instead.

        $link = [
          'title' => $this->getTitle($child),
          'url' => Url::fromRoute($route_name, $route_parameters),
          'localized_options' => $child->getOptions($this->routeMatch),
        ];
        $access = $this->accessManager->checkNamedRoute($route_name, $route_parameters, $this->account, TRUE);
        $build[$level][$plugin_id] = [
          '#theme' => 'menu_local_task',
          '#link' => $link,
          '#active' => $active,
          '#weight' => $child->getWeight(),
          '#access' => $access,
        ];
        $cacheability->addCacheableDependency($access)
          ->addCacheableDependency($child);
      }
    }
  }

}
