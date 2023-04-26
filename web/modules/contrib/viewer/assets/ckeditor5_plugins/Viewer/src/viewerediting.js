import {Plugin} from 'ckeditor5/src/core';
import {toWidget, Widget} from 'ckeditor5/src/widget';
import ViewerCommand from './viewercommand';

/**
 * Viewer editing functionality.
 */
export default class ViewerEditing extends Plugin {

  /**
   * @inheritdoc
   */
  static get requires() {
    return [Widget];
  }

  /**
   * @inheritdoc
   */
  init() {
    this.attrs = {
      ViewerId: 'data-viewer',
    };
    const options = this.editor.config.get('Viewer');
    if (!options) {
      return;
    }
    const {previewURL, themeError} = options;
    this.previewUrl = previewURL;
    this.themeError =
      themeError ||
      `
      <p>${Drupal.t(
        'An error occurred while trying to preview the Viewer. Please save your work and reload this page.',
      )}<p>
    `;

    this._defineSchema();
    this._defineConverters();

    this.editor.commands.add(
      'Viewer',
      new ViewerCommand(this.editor),
    );
  }

  /**
   * Fetches the preview.
   */
  async _fetchPreview(modelElement) {
    const query = {
      viewer: modelElement.getAttribute('ViewerId'),
    };
    const response = await fetch(
      `${this.previewUrl}?${new URLSearchParams(query)}`
    );
    if (response.ok) {
      return await response.text();
    }

    return this.themeError;
  }

  /**
   * Registers Viewer as a block element in the DOM converter.
   */
  _defineSchema() {
    const schema = this.editor.model.schema;
    schema.register('Viewer', {
      allowWhere: '$block',
      isObject: true,
      isContent: true,
      isBlock: true,
      allowAttributes: Object.keys(this.attrs),
    });
    this.editor.editing.view.domConverter.blockElements.push('viewer');
  }

  /**
   * Defines handling of drupal media element in the content lifecycle.
   *
   * @private
   */
  _defineConverters() {
    const conversion = this.editor.conversion;

    conversion
      .for('upcast')
      .elementToElement({
        model: 'Viewer',
        view: {
          name: 'viewer',
        },
      });

    conversion
      .for('dataDowncast')
      .elementToElement({
        model: 'Viewer',
        view: {
          name: 'viewer',
        },
      });
    conversion
      .for('editingDowncast')
      .elementToElement({
        model: 'Viewer',
        view: (modelElement, {writer}) => {
          const container = writer.createContainerElement('figure');
          return toWidget(container, writer, {
            label: Drupal.t('Viewer'),
          });

        },
      })
      .add((dispatcher) => {
        const converter = (event, data, conversionApi) => {
          const viewWriter = conversionApi.writer;
          const modelElement = data.item;
          const container = conversionApi.mapper.toViewElement(data.item);
          const Viewer = viewWriter.createRawElement('div', {
            'data-viewer-preview': 'loading',
            'class': 'viewer-preview'
          });
          viewWriter.insert(viewWriter.createPositionAt(container, 0), Viewer);
          this._fetchPreview(modelElement).then((preview) => {
            if (!Viewer) {
              return;
            }
            this.editor.editing.view.change((writer) => {
              const ViewerPreview = writer.createRawElement(
                'div',
                {'class': 'viewer-preview', 'data-viewer-preview': 'ready'},
                (domElement) => {
                  domElement.innerHTML = preview;
                },
              );
              writer.insert(writer.createPositionBefore(Viewer), ViewerPreview);
              writer.remove(Viewer);
            });
          });
        };
        dispatcher.on('attribute:ViewerId:Viewer', converter);
        return dispatcher;
      });

    Object.keys(this.attrs).forEach((modelKey) => {
      const attributeMapping = {
        model: {
          key: modelKey,
          name: 'Viewer',
        },
        view: {
          name: 'viewer',
          key: this.attrs[modelKey],
        },
      };
      conversion.for('dataDowncast').attributeToAttribute(attributeMapping);
      conversion.for('upcast').attributeToAttribute(attributeMapping);
    });
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'ViewerEditing';
  }
}
