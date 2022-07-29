import { RenderContext } from '@storybook/store';
import { StoryFn } from '@storybook/csf';
import { ServerFramework } from './types';
export declare const render: StoryFn<ServerFramework>;
export declare function renderToDOM({ id, title, name, showMain, showError, forceRemount, storyFn, storyContext, storyContext: { parameters, args, argTypes }, }: RenderContext<ServerFramework>, domElement: HTMLElement): Promise<void>;
