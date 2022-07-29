"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _compiler = require("../lib/compiler");

var _default = function _default(content) {
  return (0, _compiler.compileCsfModule)(JSON.parse(content));
};

exports.default = _default;