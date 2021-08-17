/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!*****************************************************!*\
  !*** ./src/js/apigee-kickstart.commerce.authnet.js ***!
  \*****************************************************/
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
/******/ })()
;