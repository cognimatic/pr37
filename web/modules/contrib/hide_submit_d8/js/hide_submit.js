/**
 * @file
 * A JavaScript file for the theme.
 *
 * This file should be used as a template for your other js files.
 * It defines a drupal behavior the "Drupal way".
 */

// JavaScript should be made compatible with libraries other than jQuery by
// wrapping it with an "anonymous closure". See:
// - https://drupal.org/node/1446420
// - http://www.adequatelygood.com/2010/3/JavaScript-Module-Pattern-In-Depth
(function($, Drupal, window, document) {
  // To understand behaviors, see https://drupal.org/node/756722#behaviors
  Drupal.behaviors.hideSubmitBlockit = {
    // eslint-disable-next-line object-shorthand
    attach: function(context, settings) {
      let timeoutId = null;

      // Reset all buttons.
      function hideSubmitResetButtons(event, form) {
        // Clear timer.
        window.clearTimeout(timeoutId);
        timeoutId = null;
        switch (settings.hide_submit.hide_submit_method) {
          case "disable":
            $(
              // eslint-disable-next-line prefer-template
              "input." +
                Drupal.checkPlain(settings.hide_submit.hide_submit_css) +
                ", button." +
                Drupal.checkPlain(settings.hide_submit.hide_submit_css),
              form
            ).each(function(i, el) {
              $(el)
                .removeClass(
                  Drupal.checkPlain(settings.hide_submit.hide_submit_hide_css)
                )
                .removeAttr("disabled");
            });
            $(".hide-submit-text", form).remove();
            break;

          case "indicator":
            // eslint-disable-next-line no-undef
            Ladda.stopAll();
            break;

          default:
            $(
              // eslint-disable-next-line prefer-template
              "input." +
                Drupal.checkPlain(settings.hide_submit.hide_submit_css) +
                ", button." +
                Drupal.checkPlain(settings.hide_submit.hide_submit_css),
              form
            ).each(function(i, el) {
              $(el)
                .stop()
                .removeClass(
                  Drupal.checkPlain(settings.hide_submit.hide_submit_hide_css)
                )
                .show();
            });
            $(".hide-submit-text", form).remove();
        }
      }

      // If the response of AJAX is form itself or document
      if (context.tagName === "FORM" || context === document) {
        $("form").each(function() {
          const $form = $(this);

          // Bind to input elements.
          if (settings.hide_submit.hide_submit_method === "indicator") {
            // Replace input elements with buttons.
            $("input.form-submit", $form).each(function() {
              const attrs = {};

              $.each($(this)[0].attributes, function(idx, attr) {
                attrs[attr.nodeName] = attr.nodeValue;
              });

              $(this).replaceWith(function() {
                return $("<button/>", attrs).append($(this).attr("value"));
              });
            });
            // Add needed attributes to the submit buttons.
            $("button.form-submit", $form).each(function() {
              $(this)
                .addClass("ladda-button button")
                .attr({
                  "data-style":
                    settings.hide_submit.hide_submit_indicator_style,
                  "data-spinner-color":
                    settings.hide_submit.hide_submit_spinner_color,
                  "data-spinner-lines":
                    settings.hide_submit.hide_submit_spinner_lines
                });
            });
            // eslint-disable-next-line no-undef
            Ladda.bind(".ladda-button", $form, {
              timeout: settings.hide_submit.hide_submit_reset_time
            });
          } else {
            $("input.form-submit, button.form-submit", $form).click(function() {
              const el = $(this);
              el.after(
                // eslint-disable-next-line prefer-template
                '<input type="hidden" name="' +
                  el.attr("name") +
                  '" value="' +
                  el.attr("value") +
                  '" />'
              );
              return true;
            });
          }

          // Bind to form submit.
          $("form").submit(function(e) {
            let $inp;
            if (!e.isPropagationStopped()) {
              if (settings.hide_submit.hide_submit_method === "disable") {
                $("input.form-submit, button.form-submit", $form)
                  .attr("disabled", "disabled")
                  .each(function() {
                    const $button = $(this);
                    if (settings.hide_submit.hide_submit_css) {
                      $button.addClass(settings.hide_submit.hide_submit_css);
                    }
                    if (settings.hide_submit.hide_submit_abtext) {
                      $button.val(
                        // eslint-disable-next-line prefer-template
                        $button.val() +
                          " " +
                          settings.hide_submit.hide_submit_abtext
                      );
                    }
                    $inp = $button;
                  });

                if ($inp && settings.hide_submit.hide_submit_atext) {
                  $inp.after(
                    // eslint-disable-next-line prefer-template
                    '<span class="hide-submit-text">' +
                      Drupal.checkPlain(
                        settings.hide_submit.hide_submit_atext
                      ) +
                      "</span>"
                  );
                }
              } else if (
                settings.hide_submit.hide_submit_method !== "indicator"
              ) {
                const pdiv =
                  // eslint-disable-next-line prefer-template
                  '<div class="hide-submit-text' +
                  (settings.hide_submit.hide_submit_hide_css
                    ? // eslint-disable-next-line prefer-template
                      " " +
                      Drupal.checkPlain(
                        settings.hide_submit.hide_submit_hide_css
                      ) +
                      '"'
                    : "") +
                  ">" +
                  Drupal.checkPlain(
                    settings.hide_submit.hide_submit_hide_text
                  ) +
                  "</div>";
                if (settings.hide_submit.hide_submit_hide_fx) {
                  $("input.form-submit, button.form-submit", $form)
                    .addClass(settings.hide_submit.hide_submit_css)
                    .fadeOut(100)
                    .eq(0)
                    .after(pdiv);
                  $("input.form-submit, button.form-submit", $form)
                    .next()
                    .fadeIn(100);
                } else {
                  $("input.form-submit, button.form-submit", $form)
                    .addClass(settings.hide_submit.hide_submit_css)
                    .hide()
                    .eq(0)
                    .after(pdiv);
                }
              }
              // Add a timeout to reset the buttons (if needed).
              if (settings.hide_submit.hide_submit_reset_time) {
                timeoutId = window.setTimeout(function() {
                  hideSubmitResetButtons(null, $form);
                }, settings.hide_submit.hide_submit_reset_time);
              }
            }
            return true;
          });
        });
      }

      if (settings.hide_submit.hide_submit_html_elements !== "") {
        $(settings.hide_submit.hide_submit_html_elements, context).click(
          function() {
            const el = $(this);
            el.after(
              // eslint-disable-next-line prefer-template
              '<span class="hide-submit-text">' +
                Drupal.checkPlain(settings.hide_submit.hide_submit_hide_text) +
                "</span>"
            );
            el.hide();
            return true;
          }
        );
      }
    }
  };
})(jQuery, Drupal, window, document);
