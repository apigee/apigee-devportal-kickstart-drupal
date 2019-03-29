<?php

/**
 * @file
 * Theme and preprocess functions for views.
 */

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

/**
 * Implements hook_form_FORM_ID_alter().
 */
function apigee_kickstart_form_views_exposed_form_alter(&$form, FormStateInterface $form_state) {
  foreach (Element::children($form) as $child) {
    if (isset($form[$child]['#theme_wrappers']['details'])) {
      $form[$child]['#theme_wrappers']['details']['#open'] = TRUE;
    }
  }

  $form['actions']['reset']['#attributes']['class'][] = 'btn-link';
  $form['actions']['reset']['#attributes']['class'][] = 'btn-reset';
}
