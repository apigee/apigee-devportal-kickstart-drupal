/**
 * @file
 * Contains Apigee Kickstart customizations for commerce_authnet module.
 */

(function ($, Drupal) {

  'use strict';

  Drupal.theme.commerceAuthorizeNetError = function (message) {
    return $('<div class="alert alert-danger"></div>').html(message);
  };

})(jQuery, Drupal);
