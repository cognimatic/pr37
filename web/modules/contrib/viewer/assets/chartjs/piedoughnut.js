(function ($, Drupal) {

  var viewer = new Viewer();

  Drupal.behaviors.ViewerPieDoughnut = {
    attach: function (context, settings) {
      $('.viewer-piedoughnut-wrapper', context).each(function() {
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
          var indexes = [];
          var bgcolors = [];
          if (settings.aggregate) {
            var index = 0;
            for (item of Object.values(configuration['datasets'])) {
              labels.push(item.label ? item.label : headers[item.dataset]);
              indexes.push(parseInt(item.dataset));
              bgcolors.push(item.color);
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
            datasets.push({
              data: items,
              label: labels,
              backgroundColor: bgcolors,
              fill: false
            });
          }
          else {
            rows.forEach(function(row, i) {
              var randomColor = Math.floor(Math.random() * 16777215).toString(16);
              labels.push(row[settings.labels]);
              bgcolors.push('#' + randomColor);
            });
            for (item of Object.values(configuration['datasets'])) {
              var items = [];
              rows.forEach(function(row, j) {
                items.push(row[item.dataset]);
              });
              datasets.push({
                data: items,
                label: headers[item.dataset],
                backgroundColor: bgcolors,//item.color,
                fill: false
              });
            }
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
