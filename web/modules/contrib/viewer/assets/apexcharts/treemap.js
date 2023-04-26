(function ($, Drupal) {

  var viewer = new Viewer();

  Drupal.behaviors.ViewerApexChartsTreemap = {
    attach: function (context, settings) {
      $('.viewer-apexcharts-treemap-wrapper', context).each(function() {
        var wrapper = $(this);
        if (wrapper.hasClass('processed')) {
          return;
        }
        wrapper.addClass('processed');
        var element = wrapper.find('.apexcharts-chart');
        viewer.load(wrapper.data('viewer'), wrapper, function(response) {
          var headers = response.data.headers;
          var rows = response.data.rows;
          var settings = response.settings;
          var configuration = response.configuration;
          var colors = [];
          var series = [];
          for (item of Object.values(configuration['datasets'])) {
            var labels = [];
            var items = [];
            rows.forEach(function(row, i) {
              items.push({
                x: row[item.labels],
                y: item.data_type == 'integer' ? parseInt(row[item.values].replace(/,/g, '')) : parseFloat(row[item.values].replace(/,/g, ''))
              });
            });
            colors.push(item.color);
            series.push({
              data: items,
              name: item.label ? item.label : headers[item.dataset],
            });
          }

          var options = {
            series: series,
            chart: {
              type: 'treemap',
              toolbar: {
                show: false,
                tools: {
                  download: false,selection: false, zoom: false, zoomin: false, zoomout: false, pan: false, customIcons: []
                },
              },
              zoom: {
                enabled: false
              }
            },
            title: {
              text: settings['chart_title'],
              align: settings['chart_title_position']
            },
            subtitle: {
              text: settings['chart_subtitle'],
              align: settings['chart_subtitle_position']
            },
            colors: colors
          };

          var chart = new ApexCharts(element[0], options);
          chart.render();

        });
      });
    }
  };

})(jQuery, Drupal);
