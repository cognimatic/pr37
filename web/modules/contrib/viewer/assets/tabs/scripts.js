(function ($, Drupal) {

  var viewer = new Viewer();

  Drupal.behaviors.ViewerTabs = {
    attach: function (context, settings) {
      $('.viewer-tabs-wrapper', context).each(function() {
        var wrapper = $(this);
        if (wrapper.hasClass('processed')) {
          return;
        }
        wrapper.addClass('processed');

        // Change tab class and display content
        var tc = wrapper.find('.tabs__tab.tabs-viewer a');
        tc.on('click', function (event) {
          event.preventDefault();
          var p = $(this).parent();
          p.closest('.tabs-wrapper').find('.is-active').removeClass('is-active');
          p.addClass('is-active');
          wrapper.find('.tabs-viewer .tabs-viewer-content').hide();
          wrapper.find('.tabs-viewer .tabs-viewer-content .viewer-table').css('width', '100%');
          $(this).addClass('is-active');
          $($(this).attr('href')).show();
        });
        wrapper.find('.tabs__tab.tabs-viewer a.is-active').trigger('click');

      });
    }
  };

})(jQuery, Drupal);
