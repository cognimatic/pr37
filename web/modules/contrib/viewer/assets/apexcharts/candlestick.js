(function ($, Drupal) {

  var viewer = new Viewer();

  Drupal.behaviors.ViewerApexChartsPieDoughnut = {
    attach: function (context, settings) {
      $('.viewer-apexcharts-candlestick-wrapper', context).each(function() {
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
          var series = [];

          rows.forEach(function(row, i) {
            series.push({
              x: new Date(parseInt(row[configuration['datasets'][0].timestamp])),
              y: [
                parseFloat(row[parseInt(configuration['datasets'][0].open)]),
                parseFloat(row[parseInt(configuration['datasets'][0].high)]),
                parseFloat(row[parseInt(configuration['datasets'][0].low)]),
                parseFloat(row[parseInt(configuration['datasets'][0].close)])
              ]
            });
          });

          var options = {
            series: [{
              data: series
            }],
            chart: {
              type: 'candlestick',
              toolbar: {
                show: true,
                tools: {
                  download: false, selection: true, zoom: true, zoomin: true, zoomout: true, pan: true, customIcons: []
                },
              },
              zoom: {
                enabled: true
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
            xaxis: {
              type: 'datetime'
            },
            yaxis: {
              tooltip: {
                enabled: true
              }
            }
          };

          var chart = new ApexCharts(element[0], options);
          chart.render();

        });
      });
    }
  };

})(jQuery, Drupal);
