import { StorybookStory, StorybookSection } from './types';
export declare function stringifyObject(object: any, level?: number, excludeOuterParams?: boolean): string;
export declare function stringifyImports(imports: Record<string, string[]>): string;
export declare function stringifyDecorators(decorators: string[]): string;
export declare function stringifyDefault(section: StorybookSection): string;
export declare function stringifyStory(story: StorybookStory): string;
export declare function stringifySection(section: StorybookSection): string;
