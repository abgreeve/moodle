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
 * Handles react import aliases
 *
 * @copyright  2025 Adrian Greeve
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


import path from "path";

const rootDir = process.cwd();

const aliasMap = {
    "@core/": path.join(rootDir, "public/lib/react/src"),
    "@calendar/": path.join(rootDir, "public/calendar/react/src"),
};

// Certain specifiers should never be bundled so we can share the compiled output.
const staticModuleRemaps = {
    "@core/react": "/lib/react/build/react.js",
    "react/jsx-runtime": "/lib/react/build/jsx-runtime.js",
    "react/jsx-dev-runtime": "/lib/react/build/jsx-dev-runtime.js",
};

function escapeRegExp(value) {
    return value.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
}

export function createAliasPlugin() {
    return {
        name: "moodle-aliases",
        setup(build) {
            Object.entries(staticModuleRemaps).forEach(([specifier, target]) => {
                const filter = new RegExp(`^${escapeRegExp(specifier)}$`);
                build.onResolve({ filter }, () => ({
                    path: target,
                    external: true,
                }));
            });

            Object.entries(aliasMap).forEach(([alias, targetDir]) => {
                const filter = new RegExp(`^${escapeRegExp(alias)}(.*)$`);
                build.onResolve({ filter }, async args => {
                    const relPath = args.path.slice(alias.length);
                    return await build.resolve(`./${relPath}`, {
                        resolveDir: targetDir,
                        kind: args.kind,
                    });
                });
            });
        }
    }
}
