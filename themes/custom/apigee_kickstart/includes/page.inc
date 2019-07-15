<?php

/**
 * @file
 * Theme and preprocess functions for page.
 */

/**
 * Implements hook_theme_suggestions_HOOK_alter().
 */
function apigee_kickstart_theme_suggestions_page_alter(&$suggestions, &$variables) {
  $current_route = \Drupal::routeMatch()->getRouteName();

  if ($current_route == 'entity.rate_plan.canonical') {
    $suggestions[] = 'page__rate_plan';
  }
}
