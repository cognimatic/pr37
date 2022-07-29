import "core-js/modules/es.symbol.js";
import "core-js/modules/es.symbol.description.js";
import "core-js/modules/es.object.to-string.js";
import "core-js/modules/es.symbol.iterator.js";
import "core-js/modules/es.array.iterator.js";
import "core-js/modules/es.string.iterator.js";
import "core-js/modules/web.dom-collections.iterator.js";
import "core-js/modules/es.array.slice.js";
import "core-js/modules/es.array.from.js";
import "core-js/modules/es.regexp.exec.js";
import "core-js/modules/es.object.freeze.js";

var _templateObject;

var _excluded = ["title", "imports", "decorators", "stories"],
    _excluded2 = ["name"];

function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }

function _taggedTemplateLiteral(strings, raw) { if (!raw) { raw = strings.slice(0); } return Object.freeze(Object.defineProperties(strings, { raw: { value: Object.freeze(raw) } })); }

function _objectWithoutProperties(source, excluded) { if (source == null) return {}; var target = _objectWithoutPropertiesLoose(source, excluded); var key, i; if (Object.getOwnPropertySymbols) { var sourceSymbolKeys = Object.getOwnPropertySymbols(source); for (i = 0; i < sourceSymbolKeys.length; i++) { key = sourceSymbolKeys[i]; if (excluded.indexOf(key) >= 0) continue; if (!Object.prototype.propertyIsEnumerable.call(source, key)) continue; target[key] = source[key]; } } return target; }

function _objectWithoutPropertiesLoose(source, excluded) { if (source == null) return {}; var target = {}; var sourceKeys = Object.keys(source); var key, i; for (i = 0; i < sourceKeys.length; i++) { key = sourceKeys[i]; if (excluded.indexOf(key) >= 0) continue; target[key] = source[key]; } return target; }

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }

import "core-js/modules/es.string.repeat.js";
import "core-js/modules/es.array.map.js";
import "core-js/modules/es.array.join.js";
import "core-js/modules/es.array.concat.js";
import "core-js/modules/es.object.keys.js";
import "core-js/modules/es.object.entries.js";
import "core-js/modules/es.array.sort.js";
import "core-js/modules/es.function.name.js";
import "core-js/modules/es.object.assign.js";
import dedent from 'ts-dedent';

var _require = require('safe-identifier'),
    identifier = _require.identifier;

export function stringifyObject(object) {
  var level = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;
  var excludeOuterParams = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;

  if (typeof object === 'string') {
    return JSON.stringify(object);
  }

  var indent = '  '.repeat(level);

  if (Array.isArray(object)) {
    var arrayStrings = object.map(function (item) {
      return stringifyObject(item, level + 1);
    });
    var arrayString = arrayStrings.join(",\n".concat(indent, "  "));
    if (excludeOuterParams) return arrayString;
    return "[\n".concat(indent, "  ").concat(arrayString, "\n").concat(indent, "]");
  }

  if (_typeof(object) === 'object') {
    var objectString = '';

    if (Object.keys(object).length > 0) {
      var objectStrings = Object.keys(object).map(function (key) {
        var value = stringifyObject(object[key], level + 1);
        return "\n".concat(indent, "  ").concat(key, ": ").concat(value);
      });
      objectString = objectStrings.join(',');
    }

    if (excludeOuterParams) return objectString;
    if (objectString.length === 0) return '{}';
    return "{".concat(objectString, "\n").concat(indent, "}");
  }

  return object;
}
export function stringifyImports(imports) {
  if (Object.keys(imports).length === 0) return '';
  return Object.entries(imports).map(function (_ref) {
    var _ref2 = _slicedToArray(_ref, 2),
        module = _ref2[0],
        names = _ref2[1];

    return "import { ".concat(names.sort().join(', '), " } from '").concat(module, "';\n");
  }).join('');
}
export function stringifyDecorators(decorators) {
  return decorators && decorators.length > 0 ? "\n  decorators: [\n    ".concat(decorators.join(',\n    '), "\n  ],") : '';
}
export function stringifyDefault(section) {
  var title = section.title,
      imports = section.imports,
      decorators = section.decorators,
      stories = section.stories,
      options = _objectWithoutProperties(section, _excluded);

  var decoratorsString = stringifyDecorators(decorators);
  var optionsString = stringifyObject(options, 0, true);
  return dedent(_templateObject || (_templateObject = _taggedTemplateLiteral(["\n  export default {\n    title: ", ",", "", "\n  };\n  \n  "])), JSON.stringify(title), decoratorsString, optionsString);
}
export function stringifyStory(story) {
  var name = story.name,
      options = _objectWithoutProperties(story, _excluded2);

  var storyId = identifier(name);
  var exportedStory = Object.assign({
    name: name
  }, options);
  var storyStrings = ["export const ".concat(storyId, " = ").concat(stringifyObject(exportedStory), ";"), ''];
  return storyStrings.join('\n');
}
export function stringifySection(section) {
  var sectionString = [stringifyImports(section.imports), stringifyDefault(section)].concat(_toConsumableArray(section.stories.map(function (story) {
    return stringifyStory(story);
  }))).join('\n');
  return sectionString;
}