(function ($, Drupal) {

  var viewer = new Viewer();

  Drupal.behaviors.ViewerFullcalendar = {
    attach: function (context, settings) {
      $('.viewer-fullcalendar-wrapper', context).each(function() {
        var wrapper = $(this);
        if (wrapper.hasClass('processed')) {
          return;
        }
        wrapper.addClass('processed');
        var element = wrapper.find('.fullcalendar-element');
        viewer.load(wrapper.data('viewer'), wrapper, function(response) {
          var settings = response.settings;
          var events = response.data.events;
          var calendar = new FullCalendar.Calendar(element[0], {
            headerToolbar: {
              left: 'prev,next today',
              center: 'title',
              right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            initialDate: settings.initial_date,
            editable: false,
            navLinks: true, // can click day/week names to navigate views
            dayMaxEvents: true, // allow "more" link when too many events
            events: events
          });
          if (settings.height) {
            calendar.setOption('height', parseInt(settings.height));
          }
          calendar.render();
        });
      });
    }
  };

})(jQuery, Drupal);
