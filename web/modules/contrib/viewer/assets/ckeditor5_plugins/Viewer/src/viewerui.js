import { Plugin } from 'ckeditor5/src/core';
import { ButtonView } from 'ckeditor5/src/ui';
import ViewerIcon from '../theme/icons/viewer.svg';
import {DomEventObserver} from "ckeditor5/src/engine";

/**
 * Ckeditor5 doesn't support double click out of the box.
 * Register it here so we can use it.
 *
 * @Todo Replace double click with a balloon style popup menu to
 *   edit the Viewer item.
 */
class DoubleClickObserver extends DomEventObserver {
  constructor( view ) {
    super( view );
    this.domEventType = 'dblclick';
  }

  onDomEvent( domEvent ) {
    this.fire( domEvent.type, domEvent );
  }
}

/**
 * Provides the Viewer button and editing.
 */
export default class ViewerUI extends Plugin {

  init() {
    const editor = this.editor;
    const options = this.editor.config.get('Viewer');
    if (!options) {
      return;
    }

    const { dialogURL, openDialog, dialogSettings = {} } = options;
    if (!dialogURL || typeof openDialog !== 'function') {
      return;
    }
    editor.ui.componentFactory.add('Viewer', (locale) => {
      const command = editor.commands.get('Viewer');
      const buttonView = new ButtonView(locale);

      buttonView.set({
        label: Drupal.t('Viewer'),
        icon: ViewerIcon,
        tooltip: true,
      });


      // Bind the state of the button to the command.
      buttonView.bind('isOn', 'isEnabled').to(command, 'value', 'isEnabled');

      this.listenTo(buttonView, 'execute', () => {
        openDialog(
          dialogURL,
          ({ attributes }) => {
            editor.execute('Viewer', attributes);
          },
          dialogSettings,
        );
      });

      return buttonView;
    });

    const view = editor.editing.view;
    const viewDocument = view.document;

    view.addObserver( DoubleClickObserver );

    editor.listenTo( viewDocument, 'dblclick', ( evt, data ) => {
      const modelElement = editor.editing.mapper.toModelElement( data.target );
      if (modelElement && typeof modelElement.name !== 'undefined' && modelElement.name === 'Viewer') {
        const query = {
          viewer: modelElement.getAttribute('ViewerId'),
        };
        openDialog(
          `${dialogURL}?${new URLSearchParams(query)}`, ({ attributes }) => {
            editor.execute('Viewer', attributes);
          },
          dialogSettings,
        );
      }
    });
  }
}
