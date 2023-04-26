(function ($, Drupal) {

  var viewer = new Viewer();

  Drupal.behaviors.ViewerPdfJs = {
    attach: function (context, settings) {
      $('.viewer-pdfjs-wrapper', context).each(function() {
        var wrapper = $(this);
        if (wrapper.hasClass('processed')) {
          return;
        }
        var element = wrapper.find('.pdfjs');
        wrapper.addClass('processed');
        viewer.load(wrapper.data('viewer'), wrapper, function(response) {
          if (response.data.file_path !== undefined) {
            var options = {
              forceIframe: true,
            };
            PDFObject.embed(response.data.file_path, element, options);
          }
          else {
            element.html(Drupal.t('PDF File not found'));
          }
        });
      });
    }
  };

})(jQuery, Drupal);
