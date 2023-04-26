import { Plugin } from 'ckeditor5/src/core';
import ViewerEditing from './viewerediting';
import ViewerUI from './viewerui';

/**
 * Main entry point to the Viewer.
 */
export default class Viewer extends Plugin {

  /**
   * @inheritdoc
   */
  static get requires() {
    return [ViewerEditing, ViewerUI];
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'Viewer';
  }
}
