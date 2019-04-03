<?php

/**
 * @file
 * Theme and preprocess functions for users.
 */

/**
 * Implements hook_theme_suggestions_HOOK_alter().
 */
function apigee_kickstart_theme_suggestions_user_alter(array &$suggestions, array $variables) {
  $view_mode = $variables['elements']['#view_mode'];
  $hook = $variables['theme_hook_original'];

  // Add a suggestion based on the view mode.
  $suggestions[] = $hook . '__' . $view_mode;
}

/**
 * Implements template_preprocess_user().
 */
function apigee_kickstart_preprocess_user(&$variables) {
  /** @var \Drupal\user\UserInterface $user */
  $user = $variables['elements']['#user'];

  // Add the view mode to the template.
  $variables['view_mode'] = $variables['elements']['#view_mode'];

  // Update the member_for field.
  if (isset($variables['content']['member_for'])) {
    $variables['content']['member_for'] = [
      '#theme' => 'pseudo_field',
      '#field' => [
        'name' => 'member_for',
        'label' => t('Member since'),
        'value' => \Drupal::service('date.formatter')->formatTimeDiffSince($user->getCreatedTime()),
      ],
    ];
  }
}
