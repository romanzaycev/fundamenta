import type {App} from "vue"

const kebabize = (str): string => str ? str.replace(/[A-Z]+(?![a-z])|[A-Z]/g, ($, ofs) => (ofs ? "-" : "") + $.toLowerCase()) : '';

const element = (blockName: string, elementName: string): string => `${blockName}__${kebabize(elementName)}`;

export type ModifierMap = Map<string, boolean | string> | {};

const iterateMods = (computedName: string, target: {}, mods?: ModifierMap): void => {
    if (mods) {
        Object.keys(mods).forEach((mod) => {
            const val = mods[mod];

            if (typeof val === "boolean") {
                target[`${computedName}_${mod}`] = val;
            } else if (typeof val == "string") {
                target[`${computedName}_${mod}-${val}`] = true;
            }
        });
    }
};

export const bem = (
    elem: false | string | ModifierMap = false,
    modsOrMixin: string | ModifierMap | {} = {},
    mods: ModifierMap = {},
    blockName: string|null = null,
): object => {
    const cn = {};

    if (elem === false && modsOrMixin) {
        cn[blockName] = true;

        if (typeof modsOrMixin === "string") {
            mods[modsOrMixin] = true;
        } else if (typeof modsOrMixin === "object") {
            Object
                .keys(modsOrMixin)
                .forEach((k) => {
                    mods[k] = modsOrMixin[k];
                });
        }

        iterateMods(blockName, cn, mods);
    } else if (typeof elem === "string") {
        const elemName = element(blockName, elem);

        cn[elemName] = true;

        if (typeof modsOrMixin === "string") {
            mods[modsOrMixin] = true;
        } else if (typeof modsOrMixin === "object") {
            Object
                .keys(modsOrMixin)
                .forEach((k) => {
                    mods[k] = modsOrMixin[k];
                });
        }

        iterateMods(elemName, cn, mods);
    } else if (!elem && !modsOrMixin && !mods) {
        cn[blockName] = true;
    }

    iterateMods(blockName, elem, mods);

    return cn;
}

export default {
    install(app: App, options) {
        app.mixin({
            methods: {
                bem(elem: false | string | ModifierMap = false,
                    modsOrMixin: string | ModifierMap | {} = {},
                    mods: ModifierMap = {},
                ) {
                    return bem(elem, modsOrMixin, mods, this.$options.name);
                },
            },
        });
    }
};
