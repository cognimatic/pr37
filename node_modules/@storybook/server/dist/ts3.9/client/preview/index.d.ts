/// <reference types="webpack-env" />
/// <reference types="node" />
import type { ClientStoryApi, Loadable } from '@storybook/addons';
import './globals';
import type { IStorybookSection, ServerFramework } from './types';
interface ClientApi extends ClientStoryApi<ServerFramework['storyResult']> {
    setAddon(addon: any): void;
    configure(loader: Loadable, module: NodeModule): void;
    getStorybook(): IStorybookSection[];
    clearDecorators(): void;
    forceReRender(): void;
    raw: () => any;
}
export declare const storiesOf: ClientApi['storiesOf'];
export declare const configure: ClientApi['configure'];
export declare const addDecorator: (() => never) | ((decorator: import("@storybook/csf").DecoratorFunction<ServerFramework, import("@storybook/addons").Args>) => void), addParameters: (({ globals, globalTypes, ...parameters }: import("@storybook/csf").Parameters & {
    globals?: import("@storybook/csf").Globals;
    globalTypes?: import("@storybook/csf").GlobalTypes;
}) => void) | (() => never), clearDecorators: (() => void) | (() => never), setAddon: ((addon: any) => void) | (() => never), getStorybook: (() => never) | (() => import("@storybook/client-api/dist/ts3.9/ClientApi").GetStorybookKind<ServerFramework>[]), raw: (() => never) | (() => import("@storybook/store").BoundStory<ServerFramework>[]);
export declare const forceReRender: (() => never) | (() => void);
export {};
