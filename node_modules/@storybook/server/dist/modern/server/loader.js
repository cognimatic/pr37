import { compileCsfModule } from '../lib/compiler';
export default (function (content) {
  return compileCsfModule(JSON.parse(content));
});