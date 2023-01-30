(function ($) {
  Drupal.behaviors.didThisHelp = {
    attach: function(context, settings) {
      $(once('did-this-help', '.did-this-help .form-submit[value="No"]', context)).each(function (index) {
        $(this).on('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          $('.did-this-help .no-choice-wrapper').toggle();

          var eTop = $('.did-this-help').offset().top;
          var blockPosition = eTop - $(window).scrollTop();
          if (blockPosition > ($(window).height() - blockPosition)) {
            var openToTop = -($('.no-choice-wrapper').height() - 44);
            $('.no-choice-wrapper').css('top', openToTop);
          }
          else {
            $('.no-choice-wrapper').css('top', 44);
          }
        });
      });

      $(once('did-this-help-click', '.did-this-help .no-choice-wrapper', context)).each(function (index) {
        $(this).on('click', function(e) {
          e.stopPropagation();
        });
      });

      $(once('did-this-help-change', '.did-this-help .no-list .no-list', context)).each(function (index) {
        $(this).on('change', function(e) {
          e.stopPropagation();

          $('.did-this-help .no-list .no-list').removeClass('selected');
          $(this).parent().addClass('selected');
          $('.did-this-help .form-item-message').appendTo($(this).parent()).show();
          $('.did-this-help .form-item-message textarea').focus();
        });
      });
    }
  };

  $('body').on('click', function() {
    $('.no-choice-wrapper').hide();
  });

}(jQuery));
