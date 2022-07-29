"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.compileCsfModule = compileCsfModule;

require("core-js/modules/es.object.assign.js");

var _stringifier = require("./stringifier");

function createSection(args) {
  return Object.assign({
    imports: {},
    decorators: []
  }, args);
}

function compileCsfModule(args) {
  return (0, _stringifier.stringifySection)(createSection(args));
}