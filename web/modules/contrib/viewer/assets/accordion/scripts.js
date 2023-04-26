(function ($, Drupal) {

  var viewer = new Viewer();

  Drupal.behaviors.ViewerAccordion = {
    attach: function (context, settings) {
      $('.viewer-accordion-wrapper', context).each(function() {
        var wrapper = $(this);
        if (wrapper.hasClass('processed')) {
          return;
        }
        wrapper.addClass('processed');

        var tc = $(this).find('.toggle');
        tc.click(function(e) {
          e.preventDefault();
          let $this = $(this);
          if ($this.next().hasClass('show')) {
            $this.next().removeClass('show');
            $this.removeClass('active');
            $this.next().slideUp(350);
          }
          else {
            $this.parent().parent().find('.toggle').removeClass('active');
            $this.parent().parent().find('li .inner').removeClass('show');
            $this.parent().parent().find('li .inner').slideUp(350);
            $this.next().toggleClass('show');
            $this.toggleClass('active');
            $this.next().slideToggle(350);
          }
        });
        $(this).find('.toggle.is-active').trigger('click');
      });
    }
  };

})(jQuery, Drupal);
