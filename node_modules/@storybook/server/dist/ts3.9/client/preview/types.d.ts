import type { StoryContext } from '@storybook/csf';
export type { RenderContext } from '@storybook/core';
export declare type StoryFnServerReturnType = any;
export declare type ServerFramework = {
    component: string;
    storyResult: StoryFnServerReturnType;
};
export declare type FetchStoryHtmlType = (url: string, id: string, params: any, context: StoryContext<ServerFramework>) => Promise<string | Node>;
export interface IStorybookStory {
    name: string;
    render: (context: any) => any;
}
export interface IStorybookSection {
    kind: string;
    stories: IStorybookStory[];
}
export interface ShowErrorArgs {
    title: string;
    description: string;
}
