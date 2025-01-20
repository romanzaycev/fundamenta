import {ModifierMap} from "@/plugins/bem-plugin";

interface FndaStoreMutation {
    storeId: string,
    events: {
        type: string,
        key: string,
        newValue: any,
        target: any,
    },
}

export enum FndaAppEnv {
    Local = 'local',
    Dev = 'dev',
    Production = 'production',
}

interface FndaAdminUiGlobals {
    env: {
        APP_ENV: FndaAppEnv | string,
        API_BASE_URI: string,
        ROUTER_BASE_PATH: string,
    },
    version: string,
}

declare global {
    const __fndaapp: FndaAdminUiGlobals;

    export function bem(
        elem?: false | string | ModifierMap,
        modsOrMixin?: string | ModifierMap | {},
        mods?: ModifierMap,
    ): object;
}
