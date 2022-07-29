"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.previewAnnotations = void 0;
exports.webpack = webpack;

var _path = _interopRequireDefault(require("path"));

var _coreCommon = require("@storybook/core-common");

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function webpack(config) {
  config.module.rules.push({
    type: 'javascript/auto',
    test: /\.stories\.json$/,
    use: _path.default.resolve(__dirname, './loader.js')
  });
  config.module.rules.push({
    type: 'javascript/auto',
    test: /\.stories\.ya?ml/,
    use: [_path.default.resolve(__dirname, './loader.js'), 'yaml-loader']
  });
  return config;
}

var previewAnnotations = function (entry = []) {
  return [...entry, (0, _coreCommon.findDistEsm)(__dirname, 'client/preview/config')];
};

exports.previewAnnotations = previewAnnotations;