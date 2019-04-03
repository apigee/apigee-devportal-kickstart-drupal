(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.fieldsetComponent = {
    attach: function (context) {
      let $fieldsets = $('fieldset', context);
      if ($fieldsets.length) {
        $fieldsets.each(function () {
          let $fieldset = $(this);
          let $toggle = $fieldset.find('legend button');
          let $content = $fieldset.find('.fieldset-wrapper');

          $toggle.on('click', function (event) {
            event.preventDefault();
            $fieldset.toggleClass('closed');
            $content.slideToggle(200);
            return false;
          });
        });
      }
    }
  };

})(jQuery, Drupal);
