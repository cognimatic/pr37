(function ($, Drupal) {

  var viewer = new Viewer();

  Drupal.behaviors.ViewerApexChartsPieDoughnut = {
    attach: function (context, settings) {
      $('.viewer-apexcharts-piedoughnut-wrapper', context).each(function() {
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
          var indexes = [];
          var colors = [];

          var index = 0;
          for (item of Object.values(configuration['datasets'])) {
            labels.push(item.label ? item.label : headers[item.dataset]);
            indexes.push(parseInt(item.dataset));
            colors.push(item.color);
          }
          // Initializing and empty martrix.
          var aggregated = new Array(rows.length).fill(0).map(() => new Array(labels.length).fill(0));
          for (var i = 0; i < rows.length; i++) {
            var k = 0;
            for (var j = 0; j < indexes.length; j++) {
              if (indexes[j] !== undefined) {
                aggregated[i][k] = parseFloat(rows[i][indexes[j]]);
                k++;
              }
            }
          }
          // Sum columns.
          var items = aggregated.reduce(function (r, a) {
            a.forEach(function (b, i) {
              r[i] = (r[i] || 0) + b;
            });
            return r;
          }, []);

          var options = {
            series: items,
            chart: {
              type: type,
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
            labels: labels,
            dataLabels: {
              enabled: true
            },
            fill: {
              gradient: {
                inverseColors: false,
                shade: 'light',
                type: "vertical",
                opacityFrom: 0.85,
                opacityTo: 0.55
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
