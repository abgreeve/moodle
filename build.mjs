// @ts-nocheck
import esbuild from "esbuild";
import { glob } from "glob";
import path from "path";
import fs from "fs";
import chalk from 'chalk';
import { createAliasPlugin } from "./.esbuild/aliases.mjs";
import { externalsPlugin } from "./.esbuild/externals.mjs";
import { generateAliases } from "./.esbuild/generate-aliases.mjs";
await generateAliases();

const args = process.argv.slice(2);
const isDev = args.includes('--dev');
const isWatch = args.includes('--watch');

console.log('═══════════════════════════════════════════');
console.log('Building Moodle React Components');
console.log('═══════════════════════════════════════════');
console.log(`Mode: ${isDev ? 'DEVELOPMENT' : 'PRODUCTION'}`);
console.log(`Watch: ${isWatch ? 'ON' : 'OFF'}`);
console.log('═══════════════════════════════════════════\n');

const sharedDefine = {
    'process.env.NODE_ENV': isDev ? '"development"' : '"production"',
};

console.log(chalk.green('> Building shared React runtime...'));

const sharedReactEntries = [
    {
        entry: "public/lib/react/src/react.ts",
        outfile: "public/lib/react/build/react.js",
    },
    {
        entry: "public/lib/react/src/profiler.ts",
        outfile: "public/lib/react/build/profiler.js",
    },
    {
        entry: "public/lib/react/src/jsx-runtime.ts",
        outfile: "public/lib/react/build/jsx-runtime.js",
    },
    {
        entry: "public/lib/react/src/jsx-dev-runtime.ts",
        outfile: "public/lib/react/build/jsx-dev-runtime.js",
    },
];

for (const { entry, outfile } of sharedReactEntries) {
    await esbuild.build({
        entryPoints: [entry],
        bundle: true,
        format: "esm",
        outfile,
        minify: !isDev,
        sourcemap: false,
        define: sharedDefine,
    });
    console.log(`${path.basename(outfile)}`);
}

console.log('\n' + chalk.green('> Building react_autoinit...'));

await esbuild.build({
    entryPoints: ["public/lib/react_autoinit/src/index.ts"],
    bundle: true,
    format: "esm",
    outfile: "public/lib/react_autoinit/build/index.js",
    external: ["react", "react-dom", "react-dom/client"],
    minify: !isDev,
    sourcemap: false,
    jsx: "automatic",
    jsxImportSource: "@moodle/core/react",
    jsxDev: isDev,
    loader: { ".ts": "tsx" },
    plugins: [createAliasPlugin(), externalsPlugin],
    define: sharedDefine,
});
console.log('react_autoinit/build/index.js');

console.log('\n' + chalk.green('> Building components...'));

// Component patterns - FIXED: Separate to avoid duplicates
const pluginComponents = glob.sync("public/!(lib)/**/react/src/**/*.tsx");
const coreComponents = glob.sync("public/lib/react/src/components/**/*.tsx");

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
    sourcemap: true,
    jsxDev: isDev,
    keepNames: isDev,
    treeShaking: !isDev,
    plugins: [createAliasPlugin(), externalsPlugin],
    define: sharedDefine,
};

// Build function
async function buildComponent(entry) {
    let part, file, output;

    // Handle different path patterns.
    if (entry.includes('/react/src/') && !entry.includes('/lib/react/src/components/')) {
        // Pattern: public/mod/book/react/src/travel.tsx (plugin components).
        part = entry.split("/react/src/")[0];
        file = entry.split("/react/src/")[1];
        output = path.join(part, "react", "build", file.replace(/\.tsx$/, ".js"));
    } else if (entry.includes('/lib/react/src/components/')) {
        // Pattern: public/lib/react/src/components/Button.tsx (core components).
        const relativePath = entry.replace('public/lib/react/src/components/', '');
        output = path.join('public/lib/react/build/components', relativePath.replace(/\.tsx$/, ".js"));
        file = `components/${relativePath}`;
    } else {
        console.warn(chalk.yellow(`Unknown path pattern: ${entry}`));
        return;
    }

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

if (isWatch) {
    console.log('═══════════════════════════════════════════');
    console.log('WATCH MODE ACTIVE');
    console.log('═══════════════════════════════════════════');
    console.log('Building initial files...\n');

    // Initial build
    for (const entry of entryPoints) {
        await buildComponent(entry);
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
        let output, file;

        if (entry.includes('/react/src/') && !entry.includes('/lib/react/src/components/')) {
            const part = entry.split("/react/src/")[0];
            file = entry.split("/react/src/")[1];
            output = path.join(part, "react", "build", file.replace(/\.tsx$/, ".js"));
        } else if (entry.includes('/lib/react/src/components/')) {
            const relativePath = entry.replace('public/lib/react/src/components/', '');
            output = path.join('public/lib/react/build/components', relativePath.replace(/\.tsx$/, ".js"));
            file = `components/${relativePath}`;
        }

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
        await buildComponent(entry);
    }
    console.log('═══════════════════════════════════════════');
    console.log('All builds complete!');
    console.log('═══════════════════════════════════════════\n');
}
