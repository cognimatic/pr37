(function ($, Drupal) {

  var viewer = new Viewer();

  Drupal.behaviors.ViewerMixedCharts = {
    attach: function (context, settings) {
      $('.viewer-mixed-wrapper', context).each(function() {
        var wrapper = $(this);
        if (wrapper.hasClass('processed')) {
          return;
        }
        wrapper.addClass('processed');
        var type = wrapper.data('type');
        var chart = wrapper.find('canvas');
        viewer.load(wrapper.data('viewer'), wrapper, function(response) {
          var headers = response.data.headers;
          var rows = response.data.rows;
          var settings = response.settings;
          var configuration = response.configuration;
          var labels = [];
          var datasets = [];
          rows.forEach(function(row, i) {
            labels.push(row[settings.labels]);
          });
          for (item of Object.values(configuration['datasets'])) {
            var items = [];
            rows.forEach(function(row, j) {
              items.push(row[item.dataset]);
            });
            datasets.push({
              data: items,
              label: item.label ? item.label : headers[item.dataset],
              borderColor: item.color,
              backgroundColor: item.color,
              type: item.type,
              order: item.weight,
              fill: false
            });
          }

          var options = {
            plugins: {
              title: {
                display: true,
                text: Drupal.t(settings['chart_title']),
                align: settings['chart_title_position'] ? settings['chart_title_position'] : 'center',
              },
              subtitle: {
                display: true,
                text: Drupal.t(settings['chart_subtitle']),
                align: settings['chart_subtitle_position'] ? settings['chart_subtitle_position'] : 'center',
              }
            }
          };

          new Chart(chart, {
            type: type,
            data: {labels: labels, datasets: datasets},
            options: options
          });

        });
      });
    }
  };

})(jQuery, Drupal);
