/**
 * @file
 * Contains Apigee Kickstart customizations for commerce_stripe module.
 */

(function ($, Drupal) {

  'use strict';

  Drupal.theme.commerceStripeError = function (message) {
    return $('<div class="alert alert-danger"></div>').html(message);
  };

})(jQuery, Drupal);
