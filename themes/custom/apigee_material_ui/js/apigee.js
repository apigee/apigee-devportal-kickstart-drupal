/**
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
(function($){
  $(function(){
    $('.sidenav').sidenav();
    //with jQuery
    $('.carousel.carousel-slider').carousel({
      fullWidth: true,
      indicators: true
    });
    $('.parallax').parallax();
    $('select').formSelect();
	$('.collapsible').collapsible();

    //adding class on scroll in nav
	if($("body").hasClass('path-frontpage') && $(".main-navigation").hasClass('transparent')){
    $(".apigee-navbar-fixed").addClass("trans");
	$("#banner").addClass("fix-banner");
    $(window).scroll(function() {
      var scroll = $(window).scrollTop();
      if ((scroll >= 350)) {
        $(".main-navigation").removeClass("transparent").addClass("white");
      } else {
        $(".main-navigation").removeClass("white").addClass("transparent");
      }
    });
    }else if($(".main-navigation").hasClass('transparent')){
    $(window).scroll(function() {
      var scroll = $(window).scrollTop();
      if ((scroll >= 50)) {
        $(".main-navigation").removeClass("transparent").addClass("white");
      } else {
        $(".main-navigation").removeClass("white").addClass("transparent");
      }
    });
    }

	if($(".navbar").hasClass('apigee-navbar-fixed')){
       $(window).scroll(function() {
      var scroll = $(window).scrollTop();
      if ((scroll >= 34)) {
        $(".navbar").addClass("sticky");
      } else {
        $(".navbar").removeClass("sticky");
      }
    });
	}
  }); // end of document ready
})(jQuery); // end of jQuery name space

// JS for status message close
(function ($) {
  'use strict';
  Drupal.behaviors.messageclose = {
    attach: function (context) {

      // Prepend a close button to each message.
      $('.messages:not(.messageclose-processed)').each(function () {
        $(this).addClass('messageclose-processed');
        $(this).prepend('<a href="#" class="messageclose" title="' + Drupal.t('close') + '">&times;</a>');
      });

      // When a close button is clicked hide this message.
      $('.messages a.messageclose').click(function (event) {
        event.preventDefault();
        $(this).parent().fadeOut('slow', function () {
          var messages_left = $('.messages__wrapper').children().size();
          if (messages_left === 1) {
            $('.messages__wrapper').remove();
          }
          else {
            $(this).remove();
          }
        });
      });

    }
  };
}(jQuery));
