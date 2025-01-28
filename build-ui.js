// noinspection JSUnusedLocalSymbols

const esbuild = require("esbuild");
const vuePlugin = require("esbuild-plugin-vue3");
const {copy} = require("esbuild-plugin-copy");
const commandLineArgs = require('command-line-args');
const {sassPlugin} = require("esbuild-sass-plugin");
const fs = require('fs');
const path = require('path');
const SVGSpriter = require('svg-sprite');
const {glob} = require('glob');

/**
 * @typedef {{
 *     watch: boolean,
 * }} BuildOptions
 */

/**
 * @type {BuildOptions}
 */
const options = commandLineArgs([
    {name: 'watch', alias: 'w', type: Boolean, defaultOption: false},
    {name: 'buildver', type: String, defaultValue: 'latest'},
]);

(async () => {
    const ctx = await esbuild.context({
        entryPoints: [
            "ui/app.ts",
        ],
        bundle: true,
        outdir: "ui-public",
        external: ["assets"],
        plugins: [
            vuePlugin({
                generateHTML: "ui/index.html",
            }),
            copy({
                resolveFrom: 'cwd',
                assets: {
                    from: ['./ui/static/*'],
                    to: ['./ui-public'],
                },
                watch: true,
            }),
            copy({
                resolveFrom: 'cwd',
                assets: {
                    from: ['./ui/assets/**/*'],
                    to: ['./ui-public/assets'],
                },
                watch: true,
            }),
            sassPlugin({
                filter: /\.scss$/,
                quietDeps: true,
                silenceDeprecations: ["legacy-js-api", "import"],
            }),
        ],
        loader: {
            '.woff': 'file',
            '.woff2': 'file',
        },
        define: {},
    });

    const spriter = new SVGSpriter({
        log: false,
        mode: {
            symbol: {
                inline: true
            }
        }
    });

    const svgs = await glob(__dirname + '/ui/svgs/**/*.svg');

    svgs.forEach((svgPath) => {
        spriter.add(
            svgPath,
            null,
            fs.readFileSync(svgPath, 'utf-8')
        );
    });

    const {result} = await spriter.compileAsync();

    for (const mode in result) {
        for (const resource in result[mode]) {
            fs.mkdirSync('ui/assets/svg-sprite', {recursive: true});
            fs.writeFileSync('ui/assets/svg-sprite/symbol.svg', result[mode][resource].contents);
        }
    }

    if (options.watch) {
        console.log('Watching...');
        await ctx.watch();
    } else {
        await ctx.rebuild();
        process.exit();
    }
})();

