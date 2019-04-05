(function($, Drupal) {

  Drupal.behaviors.customizerSettingsForm = {
    attach(context, settings) {
      const $form = $('.theme-customizer-form');
      if ($form.length) {
        const $picker = $form.find('#farbtastic-wrapper');
        const $fab = $.farbtastic($picker);
        $form.find('[type="color"]').each(function () {
          attachColorPicker($(this));
        }).focus(function () {
          attachColorPicker($(this));
        }).change(function () {
          $("body").get(0).style.setProperty($(this).attr('name'), $(this).val());
        });

        function attachColorPicker(el) {
          $fab.linkTo(function (color) {
            el.css({ backgroundColor: color, color: (this.hsl[2] > 0.5 ? '#000' : '#fff') })
              .val(color)
              .change();
          }).setColor(el.val());
        }
      }
    }
  }
})(jQuery, Drupal);
