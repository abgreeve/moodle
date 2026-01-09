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
 * Build code for bundling React
 *
 * @copyright  2026 Adrian Greeve
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import esbuild from "esbuild";
import path from "node:path";
import chalk from 'chalk';

export async function buildReact() {

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
            minify: true,
            sourcemap: false,
            define: {'process.env.NODE_ENV': '"production"'},
        });
        console.log(`${path.basename(outfile)}`);
    }
}
