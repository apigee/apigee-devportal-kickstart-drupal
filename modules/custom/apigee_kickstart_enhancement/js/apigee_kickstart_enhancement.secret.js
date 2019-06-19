/*
 * Copyright 2018 Google Inc.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 2 as published by the
 * Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public
 * License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 */

/**
 * @file
 * Script for the Secret element.
 */
(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.SecretElement = {
    attach: function (context, settings) {
      let $secret = $('.secret', context);
      if ($secret.length) {
        $secret.each(function () {
          let $this = $(this);

          // Hide the value.
          $this.addClass('secret--hidden');

          // Toggle secret.
          $(this).find('.secret__toggle').on('click', function (event) {
            event.preventDefault();
            $this.toggleClass('secret--hidden')
          });

          // Copy to clipboard.
          let $copy = $(this).find('.secret__copy');
          $copy.find('button').on('click', function (event) {
            copyToClipboard($(this).data().value);
            $copy.find('.badge').fadeIn().delay(1000).fadeOut();
          })
        });
      }
    }
  };

  /**
   * Cross browser helper to copy to clipboard.
   */
  function copyToClipboard(text) {
    if (window.clipboardData && window.clipboardData.setData) {
      // IE specific code path to prevent textarea being shown while dialog is visible.
      return clipboardData.setData("Text", text);

    } else if (document.queryCommandSupported && document.queryCommandSupported("copy")) {
      var textarea = document.createElement("textarea");
      textarea.textContent = text;
      // Prevent scrolling to bottom of page in MS Edge.
      textarea.style.position = "fixed";
      document.body.appendChild(textarea);
      textarea.select();
      try {
        // Security exception may be thrown by some browsers.
        return document.execCommand("copy");
      } catch (ex) {
        return false;
      } finally {
        document.body.removeChild(textarea);
      }
    }
  }

})(jQuery, Drupal);
