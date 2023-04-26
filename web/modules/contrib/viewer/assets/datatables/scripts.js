(function ($, Drupal) {

  var viewer = new Viewer();

  Drupal.behaviors.ViewerDatatables = {
    attach: function (context, settings) {
      $('.viewer-datatables-wrapper', context).each(function() {
        var wrapper = $(this);
        if (wrapper.hasClass('processed')) {
          return;
        }
        wrapper.addClass('processed');
        var table = wrapper.find('table');
        viewer.load(wrapper.data('viewer'), wrapper, function(response) {
          var headers = [];
          if (response.data.headers !== undefined) {
            response.data.headers.forEach(function(header) {
              headers.push({title: header});
            });
          }
          table.DataTable({
            data: response.data.rows,
            columns: headers,
            'language': {
              'info': Drupal.t('Showing page _PAGE_ of _PAGES_'),
              'infoEmpty': '',
              'emptyTable': Drupal.t('No data to display'),
              'lengthMenu': Drupal.t('Show _MENU_ records'),
              'paginate': {
                'next': Drupal.t('Next'),
                'previous': Drupal.t('Previous')
              }
            }
          });

        });
      });
    }
  };

})(jQuery, Drupal);
