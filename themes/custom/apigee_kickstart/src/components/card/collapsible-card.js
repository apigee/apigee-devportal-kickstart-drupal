(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.collapsibleCard = {
    attach: function (context) {
      let $collapsibleCards = $('.collapsible-card', context);
      if ($collapsibleCards.length) {
        $collapsibleCards.each(function () {
          let $collapsibleCard = $(this);
          let $toggle = $collapsibleCard.find('.collapsible-card__toggle');
          let $content = $collapsibleCard.find('.collapsible-card__content');

          $toggle.on('click', function (event) {
            event.preventDefault();
            $collapsibleCard.toggleClass('collapsible-card--active');
            $content.slideToggle(200);
          });
        });
      }
    }
  };

})(jQuery, Drupal);
