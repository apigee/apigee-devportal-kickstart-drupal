<?php

/**
 * @file
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

/**
 * @file
 * Preprocessors and theme functions for the Apigee Kickstart Enhancement module.
 */

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Render\Element;

/**
 * Preprocess variables for the developer_app template.
 */
function template_preprocess_app(&$variables) {
  // Add fields as content to template.
  $variables += ['content' => []];
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }

  // Add the view_mode to the template.
  $variables['view_mode'] = $variables['elements']['#view_mode'];

  // Add the entity to the template.
  if ($entity = _apigee_kickstart_enhancement_get_app_entity_from_element($variables['elements'])) {
    $variables['entity'] = $variables['elements']["#{$entity->getEntityTypeId()}"];
  }
}

/**
 * Preprocess variables for the apidoc template.
 */
function template_preprocess_apidoc(&$variables) {
  // Add fields as content to template.
  $variables += ['content' => []];
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }

  // Add the view_mode to the template.
  $variables['view_mode'] = $variables['elements']['#view_mode'];

  // Add the entity to the template.
  /** @var \Drupal\Core\Entity\EntityInterface $entity */
  $variables['entity'] = $entity = $variables['elements']["#apidoc"];

  // Add a link to the entity.
  if ($variables['view_mode'] !== 'full') {
    $variables['content']['field_link'] = $entity->toLink(t('View Documentation'))
      ->toRenderable();
  }
}

/**
 * Preprocess variables for the secret element template.
 */
function template_preprocess_secret(&$variables) {
  $variables['value'] = [
    '#markup' => $variables['elements']['#value'],
  ];
}

/**
 * Implements hook_theme_suggestions_HOOK_alter().
 */
function apigee_kickstart_enhancement_theme_suggestions_app_alter(array &$suggestions, array $variables) {
  $view_mode = $variables['elements']['#view_mode'];
  $hook = $variables['theme_hook_original'];

  // Add a suggestion based on the entity type.
  if (($entity = _apigee_kickstart_enhancement_get_app_entity_from_element($variables['elements'])) && ($entity_type_id = $entity->getEntityTypeId())) {
    $suggestions[] = $hook . '__' . $entity_type_id;

    // Add a suggestion based on the view mode.
    $suggestions[] = $hook . '__' . $view_mode;
    $suggestions[] = $hook . '__' . $entity_type_id . '__' . $view_mode;
  }
}

/**
 * Implements hook_theme_suggestions_HOOK_alter().
 */
function apigee_kickstart_enhancement_theme_suggestions_apidoc_alter(array &$suggestions, array $variables) {
  $view_mode = $variables['elements']['#view_mode'];
  $hook = $variables['theme_hook_original'];
  /** @var \Drupal\Core\Entity\EntityInterface $entity */
  $entity = $variables['elements']["#apidoc"];
  $entity_type_id = $entity->getEntityTypeId();

  // Add a suggestion based on the entity type.
  $suggestions[] = $hook . '__' . $entity_type_id;

  // Add a suggestion based on the view mode.
  $suggestions[] = $hook . '__' . $view_mode;
  $suggestions[] = $hook . '__' . $entity_type_id . '__' . $view_mode;
}

/**
 * Helper to get the app entity from an element array.
 *
 * @param array $element
 *   An element array.
 *
 * @return \Drupal\Core\Entity\EntityInterface|null
 *   The app entity or null.
 */
function _apigee_kickstart_enhancement_get_app_entity_from_element(array $element): ?EntityInterface {
  foreach (\Drupal::service('apigee_kickstart.enhancer')->getAppEntityTypes() as $entity_type_id => $app_entity_type) {
    if (isset($element["#$entity_type_id"])) {
      return $element["#$entity_type_id"];
    }
  }

  return NULL;
}
