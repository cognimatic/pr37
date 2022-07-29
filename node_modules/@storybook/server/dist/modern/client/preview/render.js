/* eslint-disable no-param-reassign */
import global from 'global';
import dedent from 'ts-dedent';
import { simulatePageLoad, simulateDOMContentLoaded } from '@storybook/preview-web';
const {
  fetch,
  Node
} = global;

const defaultFetchStoryHtml = async (url, path, params, storyContext) => {
  const fetchUrl = new URL(`${url}/${path}`);
  fetchUrl.search = new URLSearchParams(Object.assign({}, storyContext.globals, params)).toString();
  const response = await fetch(fetchUrl);
  return response.text();
};

const buildStoryArgs = (args, argTypes) => {
  const storyArgs = Object.assign({}, args);
  Object.keys(argTypes).forEach(key => {
    const argType = argTypes[key];
    const {
      control
    } = argType;
    const controlType = control && control.type.toLowerCase();
    const argValue = storyArgs[key];

    switch (controlType) {
      case 'date':
        // For cross framework & language support we pick a consistent representation of Dates as strings
        storyArgs[key] = new Date(argValue).toISOString();
        break;

      case 'object':
        // send objects as JSON strings
        storyArgs[key] = JSON.stringify(argValue);
        break;

      default:
    }
  });
  return storyArgs;
};

export const render = args => {};
export async function renderToDOM({
  id,
  title,
  name,
  showMain,
  showError,
  forceRemount,
  storyFn,
  storyContext,
  storyContext: {
    parameters,
    args,
    argTypes
  }
}, domElement) {
  // Some addons wrap the storyFn so we need to call it even though Server doesn't need the answer
  storyFn();
  const storyArgs = buildStoryArgs(args, argTypes);
  const {
    server: {
      url,
      id: storyId,
      fetchStoryHtml = defaultFetchStoryHtml,
      params
    }
  } = parameters;
  const fetchId = storyId || id;
  const storyParams = Object.assign({}, params, storyArgs);
  const element = await fetchStoryHtml(url, fetchId, storyParams, storyContext);
  showMain();

  if (typeof element === 'string') {
    domElement.innerHTML = element;
    simulatePageLoad(domElement);
  } else if (element instanceof Node) {
    // Don't re-mount the element if it didn't change and neither did the story
    if (domElement.firstChild === element && forceRemount === false) {
      return;
    }

    domElement.innerHTML = '';
    domElement.appendChild(element);
    simulateDOMContentLoaded();
  } else {
    showError({
      title: `Expecting an HTML snippet or DOM node from the story: "${name}" of "${title}".`,
      description: dedent`
        Did you forget to return the HTML snippet from the story?
        Use "() => <your snippet or node>" or when defining the story.
      `
    });
  }
}