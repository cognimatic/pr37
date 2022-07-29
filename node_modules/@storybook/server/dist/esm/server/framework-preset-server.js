import path from 'path';
import { findDistEsm } from '@storybook/core-common';
export function webpack(config) {
  config.module.rules.push({
    type: 'javascript/auto',
    test: /\.stories\.json$/,
    use: path.resolve(__dirname, './loader.js')
  });
  config.module.rules.push({
    type: 'javascript/auto',
    test: /\.stories\.ya?ml/,
    use: [path.resolve(__dirname, './loader.js'), 'yaml-loader']
  });
  return config;
}
export var previewAnnotations = function (entry = []) {
  return [...entry, findDistEsm(__dirname, 'client/preview/config')];
};