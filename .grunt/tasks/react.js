// @ts-nocheck
const path = require('path');
const fs = require('fs');

module.exports = grunt => {

    /**
     * Build a single React component using esbuild needed by the reactbuild task for watch mode.
     * Watch mode only builds single files that have changed in development mode.
     */
    const buildSingleComponent = async (filePath) => {
        const esbuild = require('esbuild');

        try {
            const aliasPlugin = await import('../../.esbuild/aliases.mjs');
            const externals = await import('../../.esbuild/externals.mjs');

            const sharedDefine = {
                'process.env.NODE_ENV': '"development"',
            };

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
                minify: false,
                sourcemap: true,
                jsxDev: true,
                keepNames: true,
                treeShaking: false,
                plugins: [aliasPlugin.createAliasPlugin(), externals.externalsPlugin],
                define: sharedDefine,
            };

            let output;
            const entry = filePath;

            if (entry.includes('/react/src/') && !entry.includes('/lib/react/src/components/')) {
                const part = entry.split("/react/src/")[0];
                const filename = entry.split("/react/src/")[1];
                output = path.join(part, "react", "build", filename.replace(/\.tsx$/, ".js").replace(/\.ts$/, ".js"));
            } else if (entry.includes('/lib/react/src/components/')) {
                const relativePath = entry.replace('public/lib/react/src/components/', '');
                output = path.join('public/lib/react/build/components', relativePath.replace(/\.tsx$/, ".js").replace(/\.ts$/, ".js"));
            } else {
                grunt.log.error('Unknown path pattern: ' + entry);
                return false;
            }

            const dir = path.dirname(output);
            if (!fs.existsSync(dir)) {
                fs.mkdirSync(dir, { recursive: true });
            }

            const now = new Date().toLocaleTimeString();

            await esbuild.build({
                ...buildConfig,
                entryPoints: [entry],
                outfile: output,
            });

            const stats = fs.statSync(output);
            grunt.log.ok(`[${now}] ${path.basename(output)}`);
            return true;
        } catch (error) {
            grunt.log.error('Build error:', error.message);
            return false;
        }
    };

    /**
     * Register reactbuild task - for watch to call
     */
    grunt.registerTask('reactbuild', 'Build React file', function() {
        const files = grunt.moodleEnv.files;

        if (!files || files.length === 0) {
            grunt.log.error('No files provided to reactbuild');
            return;
        }

        const done = this.async();

        (async () => {
            for (const file of files) {
                if (file.match(/\/react\/src\/.*\.(ts|tsx)$/)) {
                    await buildSingleComponent(file);
                }
            }
            done();
        })();
    });

    /**
     * Register react task - Build all
     */
    grunt.registerTask('react', 'Build all React components', function(mode) {
        const done = this.async();
        const spawn = require('child_process').spawn;

        const isDev = mode === 'dev';
        const args = isDev ? ['build.mjs', '--dev'] : ['build.mjs'];

        grunt.log.writeln(`Building React components in ${isDev ? 'DEVELOPMENT' : 'PRODUCTION'} mode...`);

        const build = spawn('node', args, {
            stdio: 'inherit',
            cwd: process.cwd()
        });

        build.on('close', (code) => {
            if (code !== 0) {
                grunt.fail.warn('React build failed');
                done(false);
            } else {
                grunt.log.ok('React build complete!');
                done();
            }
        });
    });

    /**
     * Configure watch
     */
    grunt.config.merge({
        watch: {
            react: {
                files: [
                    'public/**/react/src/**/*.ts',
                    'public/**/react/src/**/*.tsx',
                ],
                tasks: ['reactbuild'],
                options: {
                    spawn: false,
                }
            }
        }
    });

    /**
     * On watch, set files for reactbuild task.
     */
    let changedFiles = Object.create(null);
    const onChange = grunt.util._.debounce(function() {
        const files = Object.keys(changedFiles);
        grunt.config('moodleEnv.files', files);
        changedFiles = Object.create(null);
    }, 200);

    grunt.event.on('watch', (action, filepath) => {
        changedFiles[filepath] = action;
        onChange();
    });

    // Add the 'react' task as a startup task.
    grunt.moodleEnv.startupTasks.push('react');
};