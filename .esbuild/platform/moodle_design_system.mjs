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
 * Build code for building the react_autoinit code
 *
 * @copyright  2026 Adrian Greeve
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import esbuild from "esbuild";
import chalk from 'chalk';
import path from "node:path";
import { externalsPlugin } from "./../externals.mjs";
import { createAliasPlugin } from "./../aliases.mjs";

export async function buildMoodleDesignSystem() {

    console.log('\n' + chalk.green('> Building moodle-design-system...'));

    const projectroot = process.cwd();

    await esbuild.build({
        entryPoints: [ path.resolve(projectroot, "design-system/dist/index.es.js") ],
        bundle: true,
        format: "esm",
        platform: 'browser',
        target: 'es2022',
        outfile: path.resolve(projectroot, "public/esm-test/moodle-design-system/0.1.0/index.js"),
        external: [
            "react",
            "react-dom",
            "react/*",
            "react/cjs/*",
            "react-dom/*",
            "react-dom/cjs/*",
            "react-dom/client",
            "react/jsx-runtime",
            "react/jsx-dev-runtime"
        ],
        minify: true,
        // plugins: [createAliasPlugin(), externalsPlugin],
        define: {'process.env.NODE_ENV': '"production"'},
    });
    console.log('esm-test/moodle-design-system/0.1.0/index.js');
}
