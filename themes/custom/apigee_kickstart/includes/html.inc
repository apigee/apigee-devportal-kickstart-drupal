<?php

/**
 * @file
 * Theme and preprocess functions for html.
 */

use Drupal\Component\Utility\Html;

/**
 * Implements template_preprocess_html().
 */
function apigee_kickstart_preprocess_html(&$variables) {
  // Add a custom class based on the route.
  if ($route_name = \Drupal::routeMatch()->getRouteName()) {
    $variables['attributes']['class'][] = 'path--' . Html::cleanCssIdentifier(str_replace('.', '-', $route_name));
  }
}
