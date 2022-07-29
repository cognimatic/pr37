import "core-js/modules/es.object.assign.js";
import { stringifySection } from './stringifier';

function createSection(args) {
  return Object.assign({
    imports: {},
    decorators: []
  }, args);
}

export function compileCsfModule(args) {
  return stringifySection(createSection(args));
}