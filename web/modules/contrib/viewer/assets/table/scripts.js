(function ($, Drupal) {

  var viewer = new Viewer();

  Drupal.behaviors.ViewerTables = {
    attach: function (context, settings) {
      $('.viewer-table-wrapper', context).each(function() {
        var wrapper = $(this);
        if (wrapper.hasClass('processed')) {
          return;
        }
        wrapper.addClass('processed');
        var table = wrapper.find('table');
        var items_per_load = wrapper.data('items-per-load');
        var load_more = wrapper.find('.viewer-load-more');
        var load_more_current = 0;
        var total_items_loaded = 0;
        var total_items = 0;
        viewer.load(wrapper.data('viewer'), wrapper, function(response) {
          // Load and build table headers.
          if (response.data.headers !== undefined) {
            response.data.headers.forEach(function(header) {
              table.find('thead').append('<th>' + header + '</th>');
            });
          }
          // Load and build table rows.
          if (response.data.rows !== undefined) {
            total_items = response.data.rows.length;
            load_more.removeClass('hidden');
            if (items_per_load > 0) {
              // Initial load.
              response.data.rows.forEach(function(row, i) {
                if (items_per_load > i) {
                  buildRow(table, row, i);
                  total_items_loaded++;
                }
              });
            }
            else {
              // Load all if no items per load is specified.
              response.data.rows.forEach(function(row, i) {
                buildRow(table, row, i);
                  total_items_loaded++;
              });
            }
          }
          // When all items are loaded on the page we need to hide the Load More button.
          if (total_items_loaded == total_items) {
            load_more.addClass('hidden');
          }
          // Show more elements based on already loaded JSON.
          load_more.click(function(e) {
            e.preventDefault();
            load_more_current = load_more_current + items_per_load;
            var loaded_count = 0;
            // Loading more on Load More click.
            response.data.rows.forEach(function(row, i) {
              if (items_per_load > loaded_count && i >= load_more_current) {
                buildRow(table, row, i);
                loaded_count++;
                total_items_loaded++;
              }
            });
            buildPeityCharts(table);
            // When all items are loaded on the page we need to hide the Load More button.
            if (total_items_loaded == total_items) {
              load_more.addClass('hidden');
            }
            // Scrolling to the bottom of the loaded container.
            $('html,body').animate({scrollTop: wrapper.find('.viewer-table-load-more').offset().top}, 'slow');
          });

          buildPeityCharts(table);
        });
      });
    }
  };

  /**
   * Find and build Peity in cells.
   */
  function buildPeityCharts(parent) {
    $('.peity-cell-chart').each(function() {
      $(this).peity($(this).data('type'));
    });
  }

  /**
   * Building table rows.
   */
  function buildRow(table, row, i) {
    var tr = $('<tr class="' + ((i % 2 == 0) ? 'even' : 'odd') + '">');
    row.forEach(function(cell) {
      if (cell === null) {
        tr.append('<td></td>');
      }
      else {
        tr.append('<td>' + cell + '</td>');
      }
    });
    table.find('tbody').append(tr);
  }

})(jQuery, Drupal);
