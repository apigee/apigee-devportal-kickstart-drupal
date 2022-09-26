<?php

/**
 * @file
 * Theme and preprocess functions for nodes.
 */

use Drupal\media\Entity\Media;
use Drupal\paragraphs\Entity\Paragraph;

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

  $apigee_api_content_types = [
    'apidoc',
    'graphql_doc',
    'asyncapi_doc',
    'freeform_doc'
  ];

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

      case 'freeform_doc':
        $variables['api_style'] = 'Free-form';
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

  // Web accessibility standards
  // When Home page - Hero section is rendered add the alternative text
  // for missing alt text for images.
  if ($node->hasfield('field_header')) {
    foreach ($node->get('field_header') as $paragraph) {
      if ($paragraph->entity->getType() == 'hero') {
        $paragraph_featured_apis = $paragraph->entity;
        if (!empty($paragraph_featured_apis)) {
          foreach ($paragraph_featured_apis->get('field_media') as $reference) {
            $media = Media::load($reference->target_id);
            $media_field = $media->get('field_media_image')->first()->getValue();
            $field_title = $paragraph_featured_apis->get('field_title')->first()->getValue();

            // Adding node title as alternative text (Web accessibility standards)
            if ($media_field["alt"] === NULL) {
              $media_field["alt"] = $field_title;
              $media->set('field_media_image', $media_field);
            }
          }
        }
      }
    }
  }

  // Web accessibility standards
  // When Home page - Featured-APIs section is rendered add the alternative text
  // for missing alt text for images.
  if ($node->hasfield('field_content')) {
    foreach ($node->get('field_content') as $paragraph) {
      if ($paragraph->entity->getType() == 'card_group') {
        $paragraph_featured_apis = $paragraph->entity;
        if (!empty($paragraph_featured_apis)) {
          foreach ($paragraph_featured_apis->get('field_cards') as $reference) {

            $paragraph_cards = Paragraph::load($reference->target_id);
            $field_image = $paragraph_cards->get('field_image')->first()->getValue();
            $field_title = $paragraph_cards->get('field_title')->first()->getValue();
            $media = Media::load($field_image['target_id']);
            $media_field = $media->get('field_media_image')->first()->getValue();

            // Adding card title as alternative text (Web accessibility standards)
            if ($media_field["alt"] === NULL) {
              $media_field["alt"] = $field_title;
              $media->set('field_media_image', $media_field);
            }
          }
        }
      }
    }
  }

  // Web accessibility standards
  // When Home page - Text and image section is rendered add the alternative text
  // for missing alt text for images.
  if ($node->hasfield('field_content')) {
    foreach ($node->get('field_content') as $paragraph) {
      if ($paragraph->entity->getType() == 'text_image') {
        $paragraph_featured_apis = $paragraph->entity;
        if (!empty($paragraph_featured_apis)) {
          foreach ($paragraph_featured_apis->get('field_image') as $reference) {
            $media = Media::load($reference->target_id);
            $media_field = $media->get('field_media_image')->first()->getValue();
            $field_title = $paragraph_featured_apis->get('field_title')->first()->getValue();

            // Adding node title as alternative text (Web accessibility standards)
            if ($media_field["alt"] === NULL) {
              $media_field["alt"] = $field_title;
              $media->set('field_media_image', $media_field);
            }
          }
        }
      }
    }
  }

  // Web accessibility standards
  // When Apis listing page is rendered add the alternative text
  // for missing alt text for images.
  if ($node->hasfield('field_image')) {
    foreach ($node->field_image as $reference) {
      $media = Media::load($reference->target_id);
      $media_field = $media->get('field_media_image')->first()->getValue();

      // Adding node title as alternative text (Web accessibility standards)
      if ($media_field["alt"] === NULL) {
        $media_field["alt"] = $node->label();
        $media->set('field_media_image', $media_field);
      }
    }
  }
}
