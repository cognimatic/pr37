import { Command } from 'ckeditor5/src/core';

/**
 * Creates Viewer
 */
function createViewer(writer, attributes) {
  return writer.createElement('Viewer', attributes);
}

/**
 * Command for inserting <viewer> tag into ckeditor.
 */
export default class ViewerCommand extends Command {
  execute(attributes) {
    const ViewerEditing = this.editor.plugins.get('ViewerEditing');
    const dataAttributeMapping = Object.entries(ViewerEditing.attrs).reduce(
      (result, [key, value]) => {
        result[value] = key;
        return result;
      },
      {},
    );
    const modelAttributes = Object.keys(attributes).reduce(
      (result, attribute) => {
        if (dataAttributeMapping[attribute]) {
          result[dataAttributeMapping[attribute]] = attributes[attribute];
        }
        return result;
      },
      {},
    );

    this.editor.model.change((writer) => {
      this.editor.model.insertContent(
        createViewer(writer, modelAttributes),
      );
    });
  }

  refresh() {
    const model = this.editor.model;
    const selection = model.document.selection;
    const allowedIn = model.schema.findAllowedParent(
      selection.getFirstPosition(),
      'Viewer',
    );
    this.isEnabled = allowedIn !== null;
  }
}
