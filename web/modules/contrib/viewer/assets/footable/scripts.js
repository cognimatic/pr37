(function ($, Drupal) {

  var viewer = new Viewer();

  Drupal.behaviors.ViewerFootable = {
    attach: function (context, settings) {
      $('.viewer-footable-wrapper', context).each(function() {
        var wrapper = $(this);
        if (wrapper.hasClass('processed')) {
          return;
        }
        wrapper.addClass('processed');
        var table = wrapper.find('table');
        viewer.load(wrapper.data('viewer'), wrapper, function(response) {
          table.footable({
            "columns": response.data.headers,
            "rows": response.data.rows
          });
        });
      });
    }
  };

})(jQuery, Drupal);
