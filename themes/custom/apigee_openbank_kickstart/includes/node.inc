<?php

/**
 * @file
 * Theme and preprocess functions for nodes.
 */

/**
 * Implements hook_preprocess_node().
 */
function apigee_openbank_kickstart_preprocess_node(&$variables) {
  $node = $variables['node'];

  // When 'field_header' is populated, build the 'tasks' theme region, allowing
  // it to be rendered within the node template.
  if ($variables['view_mode'] == 'full' && $node->hasField('field_header') && $node->field_header->getValue()) {
    apigee_openbank_kickstart_add_regions_to_node(['tasks'], $variables);
  }
}
