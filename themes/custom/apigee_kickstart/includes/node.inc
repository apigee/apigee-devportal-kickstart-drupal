<?php

/**
 * @file
 * Theme and preprocess functions for nodes.
 */

/**
 * Implements hook_preprocess_node().
 */
function apigee_kickstart_preprocess_node(&$variables) {
  $node = $variables['node'];

  // When 'field_header' is populated, build the 'tasks' theme region, allowing
  // it to be rendered within the node template.
  if ($variables['view_mode'] == 'full' && $node->hasField('field_header') && $node->field_header->getValue()) {
    apigee_kickstart_add_regions_to_node(['tasks'], $variables);
  }

  // Allow sidebar regions to be printed in node templates.
  // Note: When printing sidebars in templates, you must ensure they don't also
  // print within the corresponding page template.
  $sidebar_types = ['article', 'forum', 'page'];
  if (in_array($node->bundle(), $sidebar_types)) {
    apigee_kickstart_add_regions_to_node(['sidebar_first', 'sidebar_second'], $variables);
  }
}
