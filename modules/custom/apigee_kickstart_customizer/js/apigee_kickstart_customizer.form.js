(function($, Drupal) {

  Drupal.behaviors.customizerForm = {
    attach(context, settings) {
      const styles = $('body').get(0).style;
      const $form = $('.customizer-form');
      const $pickerWrapper = $form.find('#farbtastic-wrapper');

      if ($form.length && $pickerWrapper.length) {
        const $picker = $.farbtastic($pickerWrapper);

        // Attach a color picker to color fields.
        $form.find('[data-picker]').each(function () {
          attachColorPicker($(this));
        }).focus(function () {
          attachColorPicker($(this));
        }).change(function () {
          styles.setProperty($(this).attr('name'), $(this).val());
        });

        /**
         * Helper to attach a color picker to the given element.
         *
         * @param el
         */
        function attachColorPicker(el) {
          $picker.linkTo(function (color) {
            el.css({ backgroundColor: color, color: (this.hsl[2] > 0.5 ? '#000' : '#fff') })
              .val(color)
              .change();
          }).setColor(el.val());
        }
      }
    }
  }
})(jQuery, Drupal);
