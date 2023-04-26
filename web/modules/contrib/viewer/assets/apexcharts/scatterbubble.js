(function ($, Drupal) {

  var viewer = new Viewer();

  Drupal.behaviors.ViewerApexChartsScatterBubble = {
    attach: function (context, settings) {
      $('.viewer-apexcharts-scatterbubble-wrapper', context).each(function() {
        var wrapper = $(this);
        if (wrapper.hasClass('processed')) {
          return;
        }
        wrapper.addClass('processed');
        var type = wrapper.data('type');
        var element = wrapper.find('.apexcharts-chart');
        viewer.load(wrapper.data('viewer'), wrapper, function(response) {
          var headers = response.data.headers;
          var rows = response.data.rows;
          var settings = response.settings;
          var configuration = response.configuration;
          var labels = [];
          var colors = [];
          var series = [];
          rows.forEach(function(row, i) {
            labels.push(row[settings.labels]);
          });
          for (item of Object.values(configuration['datasets'])) {
            var items = [];
            rows.forEach(function(row, j) {
              items.push(row[item.dataset].split(',').map(parseFloat));
            });
            colors.push(item.color);
            series.push({
              data: items,
              name: item.label ? item.label : headers[item.dataset],
              type: item.type
            });
          }

          var options = {
            series: series,
            chart: {
              toolbar: {
                show: false,
                tools: {
                  download: false,selection: false, zoom: false, zoomin: false, zoomout: false, pan: false, customIcons: []
                },
              },
              zoom: {
                enabled: false
              },
              type: type
            },
            dataLabels: {
              enabled: false
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
