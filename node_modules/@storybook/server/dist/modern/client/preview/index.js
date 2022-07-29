import { start } from '@storybook/core';
import './globals';
import { renderToDOM, render } from './render';
const framework = 'server';
const api = start(renderToDOM, {
  render
});
export const storiesOf = (kind, m) => {
  return api.clientApi.storiesOf(kind, m).addParameters({
    framework
  });
};
export const configure = (...args) => api.configure(framework, ...args);
export const {
  addDecorator,
  addParameters,
  clearDecorators,
  setAddon,
  getStorybook,
  raw
} = api.clientApi;
export const {
  forceReRender
} = api;