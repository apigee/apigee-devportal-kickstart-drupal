<?php

/**
 * @file
 * Theme and preprocess functions for forms.
 */

use Drupal\apigee_m10n_add_credit\AddCreditConfig;
use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Implements hook_theme_suggestions_HOOK_alter().
 */
function apigee_kickstart_theme_suggestions_input_alter(array &$suggestions, array $variables) {
  // Add a suggestion for submit button based on the untranslated value.
  if ($variables['element']['#type'] == 'submit') {
    /** @var \Drupal\Core\StringTranslation\TranslatableMarkup $value */
    if (($value = $variables['element']['#value']) && ($value instanceof TranslatableMarkup) && ($untranslated = $value->getUntranslatedString())) {
      $suggestions[] = $variables['theme_hook_original'] . '__' . Html::cleanCssIdentifier(strtolower($untranslated));
    }
  }
}

/**
 * Implements hook_form_alter().
 */
function apigee_kickstart_form_alter(array &$form, FormStateInterface $form_state, $form_id) {
  if (strpos($form_id, 'commerce_order_item_add_to_cart_form') === 0) {
    $order_item = $form_state->getFormObject()->getEntity();
    $purchased_entity = $order_item->getPurchasedEntity();
    $product = $purchased_entity->getProduct();

    // Update the label for the unit price for add_credit products.
    if ($product->get(AddCreditConfig::ADD_CREDIT_ENABLED_FIELD_NAME)->value) {
      $form['unit_price']['widget'][0]['amount']['#title'] = t('Amount to be added to your account balance');
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

/**
 * Implements hook_form_FORM_ID_alter().
 */
function apigee_kickstart_form_commerce_checkout_flow_multistep_default_alter(array &$form, FormStateInterface $form_state) {
  $step = $form['#step_id'];

  // Add a class for the step.
  $form['#attributes']['class'][] = 'commerce-checkout-flow--' . Html::cleanCssIdentifier($step);

  switch ($step) {
    case 'review':
      $form['review']['title'] = [
        '#type' => 'html_tag',
        '#value' => t('Review Order'),
        '#tag' => 'h3',
        '#weight' => -100,
      ];
      break;

    case 'complete':
      $order = $form_state->getFormObject()->getOrder();
      /** @var \Drupal\commerce_order\Entity\OrderItemInterface $item */
      foreach ($order->getItems() as $item) {
        $purchased_entity = $item->getPurchasedEntity();
        $product = $purchased_entity->getProduct();

        // If an add credit product was checkout, add a link to the balance page.
        if ($product->get(AddCreditConfig::ADD_CREDIT_ENABLED_FIELD_NAME)->value) {
          $form['go_to_balance'] = Link::createFromRoute(t('Go to Balance'), 'apigee_monetization.my_billing', [], [
            'attributes' => [
              'class' => [
                'btn',
                'btn-lg',
                'btn-primary'
              ],
            ],
          ])->toRenderable();
        }
      }
      break;
  }
}
