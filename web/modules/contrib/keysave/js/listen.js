(function (Drupal, drupalSettings, once) {
  'use strict';

  Drupal.behaviors.keySave = {
    attach: function (context, settings) {
      // Identify the primary submit button based on its data attribute.
      let save_btn = document.querySelector('[data-drupal-selector="edit-submit"]');
      if (save_btn == null) {
        save_btn = document.querySelector('[data-drupal-selector="edit-actions-submit"]');
      }
      if (save_btn == null) {
        save_btn = document.querySelector('[data-drupal-selector="edit-save"]');
      }
      if (save_btn == null) {
        // If we don't know where to click, don't attach a listener.
        return;
      }
      // Add event listener on keydown
      document.addEventListener('keydown', (event) => {
        // Trigger the save and prevent browser default behaviour (save dialog).
        if ((event.ctrlKey || event.metaKey) && event.code == 'KeyS') {
          save_btn.click();
          event.preventDefault();
        }
      }, false);

      Drupal.keySaveCKEditor.init(context);
    }
  };

  Drupal.keySaveCKEditor = {
    init: (context) => {
      once('keySaveCKEditors', 'body', context).forEach(() => {
        if (window.CKEDITOR && CKEDITOR !== undefined) {
          CKEDITOR.on('instanceReady', (element) => {
            const save_btn = document.querySelector('[data-drupal-selector="edit-submit"]');

            element.editor.on('key', function (event) {
              if (event.data.keyCode == CKEDITOR.CTRL + 83 || event.data.keyCode == 1114195) {
                save_btn.click();
                event.cancel();
              }
            });
          });
        }
      });
    }
  };

} (Drupal, drupalSettings, once));
