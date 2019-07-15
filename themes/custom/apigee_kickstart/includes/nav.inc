<?php

/**
 * @file
 * Theme and preprocess functions for local tasks and actions.
 */

/**
 * Implements template_preprocess_menu_local_action().
 */
function apigee_kickstart_preprocess_menu_local_action(&$variables) {
  $route_match = Drupal::routeMatch();

  // Add the route name to the template.
  $variables['route_name'] = $route_match->getRouteName();

  // Add the current page title to the template.
  $variables['title'] = \Drupal::service('title_resolver')->getTitle(Drupal::request(), $route_match->getRouteObject());
}
