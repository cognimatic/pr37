(function ($, Drupal) {

  Drupal.behaviors.ViewerTabsAdmin = {
    attach: function (context, settings) {
      $("input[name^='tabs'][name$='[default]']").on('click', function(e) {
        $("input[name^='tabs'][name$='[default]']").not(this).prop('checked', false);
      });
    }
  };

})(jQuery, Drupal);
