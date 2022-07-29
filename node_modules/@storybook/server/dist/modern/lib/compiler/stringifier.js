const _excluded = ["title", "imports", "decorators", "stories"],
      _excluded2 = ["name"];

function _objectWithoutPropertiesLoose(source, excluded) { if (source == null) return {}; var target = {}; var sourceKeys = Object.keys(source); var key, i; for (i = 0; i < sourceKeys.length; i++) { key = sourceKeys[i]; if (excluded.indexOf(key) >= 0) continue; target[key] = source[key]; } return target; }

import dedent from 'ts-dedent';

const {
  identifier
} = require('safe-identifier');

export function stringifyObject(object, level = 0, excludeOuterParams = false) {
  if (typeof object === 'string') {
    return JSON.stringify(object);
  }

  const indent = '  '.repeat(level);

  if (Array.isArray(object)) {
    const arrayStrings = object.map(item => stringifyObject(item, level + 1));
    const arrayString = arrayStrings.join(`,\n${indent}  `);
    if (excludeOuterParams) return arrayString;
    return `[\n${indent}  ${arrayString}\n${indent}]`;
  }

  if (typeof object === 'object') {
    let objectString = '';

    if (Object.keys(object).length > 0) {
      const objectStrings = Object.keys(object).map(key => {
        const value = stringifyObject(object[key], level + 1);
        return `\n${indent}  ${key}: ${value}`;
      });
      objectString = objectStrings.join(',');
    }

    if (excludeOuterParams) return objectString;
    if (objectString.length === 0) return '{}';
    return `{${objectString}\n${indent}}`;
  }

  return object;
}
export function stringifyImports(imports) {
  if (Object.keys(imports).length === 0) return '';
  return Object.entries(imports).map(([module, names]) => `import { ${names.sort().join(', ')} } from '${module}';\n`).join('');
}
export function stringifyDecorators(decorators) {
  return decorators && decorators.length > 0 ? `\n  decorators: [\n    ${decorators.join(',\n    ')}\n  ],` : '';
}
export function stringifyDefault(section) {
  const {
    title,
    decorators
  } = section,
        options = _objectWithoutPropertiesLoose(section, _excluded);

  const decoratorsString = stringifyDecorators(decorators);
  const optionsString = stringifyObject(options, 0, true);
  return dedent`
  export default {
    title: ${JSON.stringify(title)},${decoratorsString}${optionsString}
  };
  
  `;
}
export function stringifyStory(story) {
  const {
    name
  } = story,
        options = _objectWithoutPropertiesLoose(story, _excluded2);

  const storyId = identifier(name);
  const exportedStory = Object.assign({
    name
  }, options);
  const storyStrings = [`export const ${storyId} = ${stringifyObject(exportedStory)};`, ''];
  return storyStrings.join('\n');
}
export function stringifySection(section) {
  const sectionString = [stringifyImports(section.imports), stringifyDefault(section), ...section.stories.map(story => stringifyStory(story))].join('\n');
  return sectionString;
}