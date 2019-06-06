<?php

/**
 * @file
 * Theme and preprocess functions for forms.
 */

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_theme_suggestions_HOOK_alter().
 */
function apigee_kickstart_theme_suggestions_input_alter(array &$suggestions, array $variables) {
  // Add a suggestion for submit button based on the untranslated value.
  if ($variables['element']['#type'] == 'submit') {
    /** @var \Drupal\Core\StringTranslation\TranslatableMarkup $value */
    if (($value = $variables['element']['#value']) && ($untranslated = $value->getUntranslatedString())) {
      $suggestions[] = $variables['theme_hook_original'] . '__' . Html::cleanCssIdentifier(strtolower($untranslated));
    }
  }
}

/**
 * Implements hook_form_FORM_ID_alter().
 */
function apigee_kickstart_form_previous_balances_reports_download_form_alter(array &$form, FormStateInterface $form_state) {
  // Move the heading out of the form.
  $form['#prefix'] = render($form['heading']);
}
