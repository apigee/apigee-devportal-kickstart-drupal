<?php

/**
 * @file
 * Theme and preprocess functions for menus.
 */

/**
 * Implements template_preprocess_menu_local_tasks().
 */
function apigee_openbank_kickstart_preprocess_menu_local_tasks(&$variables) {
  // On user pages, the primary tabs are rendered as secondary.
  // Swap primary with secondary.
  if (in_array(Drupal::routeMatch()->getRouteName(), ['user.register', 'user.pass', 'user.login'])
  && (empty($variables['secondary']))) {
    $variables['secondary'] = $variables['primary'];
    unset($variables['primary']);
  }
}
