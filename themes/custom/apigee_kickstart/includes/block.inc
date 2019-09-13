<?php

/**
 * @file
 * Theme and preprocess functions for blocks.
 */

use Drupal\block\Entity\Block;

/**
 * Implements hook_theme_suggestions_HOOK_alter().
 *
 * @see block_theme_suggestions_block()
 */
function apigee_kickstart_theme_suggestions_block_alter(array &$suggestions, array $variables) {
  if (!empty($variables['elements']['#id'])) {
    $block = Block::load($variables['elements']['#id']);

    // Recreate the same suggestions as Drupal core, including the region.
    $suggestions[] = 'block__' . $variables['elements']['#configuration']['provider'] . '__' . $block->getRegion();

    $parts = explode(':', $variables['elements']['#plugin_id']);
    $suggestion = 'block';
    while ($part = array_shift($parts)) {
      $suggestions[] = $suggestion .= '__' . strtr($part, '-', '_') . '__' . $block->getRegion();
    }
    if (!empty($variables['elements']['#id'])) {
      $suggestions[] = 'block__' . $variables['elements']['#id'] . '__' . $block->getRegion();
    }
  }
  return $suggestions;
}
