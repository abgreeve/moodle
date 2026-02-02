// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Build code for bundling plugin components
 *
 * @copyright  2026 Adrian Greeve
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import esbuild from "esbuild";
import { glob } from "glob";
import chalk from 'chalk';
import path from "path";
import fs from "fs";

import { createAliasPlugin } from "./../aliases.mjs";
import { externalsPlugin } from "./../externals.mjs";

const projectroot = process.cwd();
const fromroot = (...segments) => path.resolve(projectroot, ...segments);

// Build function
async function buildComponent(entry, isWatch, buildConfig) {

    const resolved = resolveComponentPaths(entry);
    if (!resolved) {
        console.warn(chalk.yellow(`Unknown path pattern: ${entry}`));
        return;
    }
    const { file, output } = resolved;

    fs.mkdirSync(path.dirname(output), { recursive: true });

    console.log(chalk.green(`>> Building: ${file}`));

    try {
        await esbuild.build({
            ...buildConfig,
            entryPoints: [entry],
            outfile: output,
        });

        const stats = fs.statSync(output);
        const mapExists = fs.existsSync(output + '.map');
        console.log(`${file} -> ${(stats.size / 1024).toFixed(2)} KB ${mapExists ? '(+ sourcemap)' : ''}\n`);
    } catch (error) {
        console.error(chalk.red(`Failed to build ${file}:`), error);
        if (!isWatch) {
            process.exit(1);
        }
    }
}

function resolveComponentPaths(entry) {
    const rel = path.relative(projectroot, entry);

    // React components
    // if (rel.includes(path.join('react', 'src')) && !rel.startsWith(path.join('public', 'lib', 'react', 'src'))) {
    if (rel.includes(path.join('react', 'src'))) {

        const part = rel.split(path.join('react', 'src'))[0];
        const file = rel.split(path.join('react', 'src'))[1].replace(/^[\/\\]/, '');

        return {
            file,
            output: fromroot(part, 'react', 'build', file.replace(/\.(ts|tsx)$/, '.js')),
        };
    }
    return null;
}


export async function buildPluginComponents(isDev, sharedDefine, isWatch) {
    console.log('\n' + chalk.green('> Building components...'));

    // Component patterns - FIXED: Separate to avoid duplicates
    const pluginComponents = glob.sync("public/!(lib)/**/react/src/**/*.tsx", {
        cwd: projectroot,
        absolute: true,
    });
    const coreComponents = glob.sync("public/lib/react/src/**/*.{ts,tsx}", {
        cwd: projectroot,
        absolute: true,
    });

    // Combine and deduplicate using Set
    const entryPoints = [...new Set([...pluginComponents, ...coreComponents])];

    console.log(`Found ${entryPoints.length} components`);
    console.log(`  - Plugin components: ${pluginComponents.length}`);
    console.log(`  - Core components: ${coreComponents.length}\n`);

    // Build config for components.
    const buildConfig = {
        bundle: true,
        format: "esm",
        loader: {
            ".tsx": "tsx",
            ".ts": "ts",
            ".jsx": "jsx",
            ".js": "js",
        },
        resolveExtensions: [".tsx", ".ts", ".jsx", ".js"],
        external: ["react", "react-dom", "react-dom/client"],
        jsx: "automatic",
        jsxImportSource: "@moodle/core/react",
        minify: !isDev,
        sourcemap: isDev ? 'inline' : false,
        jsxDev: isDev,
        keepNames: isDev,
        treeShaking: !isDev,
        plugins: [createAliasPlugin(), externalsPlugin],
        define: sharedDefine,
    };

    if (isWatch) {
        console.log('═══════════════════════════════════════════');
        console.log('WATCH MODE ACTIVE');
        console.log('═══════════════════════════════════════════');
        console.log('Building initial files...\n');

        // Initial build
        for (const entry of entryPoints) {
            await buildComponent(entry, isWatch, buildConfig);
        }

        console.log('═══════════════════════════════════════════');
        console.log('Watching for changes...');
        console.log('Files:');
        console.log('  - public/!(lib)/**/react/src/**/*.tsx (plugins)');
        console.log('  - public/lib/react/src/components/**/*.tsx (core)');
        console.log('Press Ctrl+C to stop');
        console.log('═══════════════════════════════════════════\n');

        // Setup watch contexts.
        const contexts = [];
        for (const entry of entryPoints) {

            const resolved = resolveComponentPaths(entry);
            if (!resolved) {
                return;
            }

            const { file, output } = resolved;

            const ctx = await esbuild.context({
                ...buildConfig,
                entryPoints: [entry],
                outfile: output,
                plugins: [
                    ...buildConfig.plugins,
                    {
                        name: 'rebuild-notifier',
                        setup(build) {
                            build.onEnd(result => {
                                const now = new Date().toLocaleTimeString();
                                if (result.errors.length === 0) {
                                    console.log(chalk.green(`[${now}] Rebuilt: ${file}`));
                                } else {
                                    console.log(chalk.red(`[${now}] Build failed: ${file}`));
                                    result.errors.forEach(err => console.error(chalk.red(err)));
                                }
                            });
                        }
                    }
                ]
            });

            await ctx.watch();
            contexts.push(ctx);
        }

        // Graceful shutdown.
        process.on('SIGINT', async () => {
            console.log('\n\n═══════════════════════════════════════════');
            console.log('Stopping watch mode...');
            for (const ctx of contexts) {
                await ctx.dispose();
            }
            console.log('Stopped');
            console.log('═══════════════════════════════════════════\n');
            process.exit(0);
        });

    } else {
        // Build all components.
        for (const entry of entryPoints) {
            await buildComponent(entry, isWatch, buildConfig);
        }
        console.log('═══════════════════════════════════════════');
        console.log('All builds complete!');
        console.log('═══════════════════════════════════════════\n');
    }
}
