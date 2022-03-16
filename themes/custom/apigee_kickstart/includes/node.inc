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
    apigee_kickstart_add_regions_to_node([
      'sidebar_first',
      'sidebar_second',
    ], $variables);
  }

  $apigee_api_content_types = ['apidoc', 'graphql_doc', 'asyncapi_doc'];

  if (in_array($node->bundle(), $apigee_api_content_types)) {

    // Add the entity to the template.
    $variables['elements']["#apidoc"] = $node;
    $variables['entity'] = $node;

    $variables['attributes']['class'][] = 'apidoc';
    $variables['attributes']['class'][] = 'apidoc--view-mode-' . $variables['view_mode'];
    switch ($node->bundle()) {
      case 'graphql_doc':
        $variables['api_style'] = 'GraphQL';
        break;

      case 'asyncapi_doc':
        $variables['api_style'] = 'AsyncAPI';
        break;

      default:
        $variables['api_style'] = 'REST';
        break;
    }

    // Add a link to the entity.
    if ($variables['view_mode'] !== 'full') {
      $variables['content']['field_apidoc_file_link'] = $node->toLink(t('View Documentation'))
        ->toRenderable();
    }
  }
}
