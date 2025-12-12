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
const publicDir = path.join(rootDir, "public");

const aliasMap = {
    "@core/": path.join(rootDir, "public/lib/react/src"),
    "@calendar/": path.join(rootDir, "public/calendar/react/src"),
    "@mod_book/": path.join(rootDir, "public/mod/book/react/src"),
};

// Certain specifiers should never be bundled so we can share the compiled output.
const staticModuleRemaps = {
    "@moodle/core/react": path.join(publicDir, "lib/react/build/react.js"),
    "react/jsx-runtime": path.join(publicDir, "lib/react/build/jsx-runtime.js"),
    "react/jsx-dev-runtime": path.join(publicDir, "lib/react/build/jsx-dev-runtime.js"),
    "@moodle/core/profiler": path.join(publicDir, "lib/react/build/profiler.js"),
};

function toBrowserPath(value) {
    return value.split(path.sep).join("/");
}

function ensureRelativeSpecifier(specifier) {
    if (specifier.startsWith(".") || specifier.startsWith("/")) {
        return specifier;
    }

    return `./${specifier}`;
}

function getOutputDirForImporter(importer) {
    const marker = `${path.sep}react${path.sep}src${path.sep}`;
    const idx = importer.lastIndexOf(marker);

    if (idx === -1) {
        return path.dirname(importer);
    }

    const before = importer.slice(0, idx);
    const after = importer.slice(idx + marker.length);
    const subDir = path.dirname(after);

    return path.join(before, "react", "build", subDir === "." ? "" : subDir);
}

function buildRelativeRuntimePath(importer, target) {
    if (!importer) {
        const relativeToPublic = path.relative(publicDir, target);
        return ensureRelativeSpecifier(toBrowserPath(relativeToPublic));
    }

    const importerOutputDir = getOutputDirForImporter(importer);
    const relativePath = path.relative(importerOutputDir, target);
    return ensureRelativeSpecifier(toBrowserPath(relativePath));
}

function escapeRegExp(value) {
    return value.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
}

export function createAliasPlugin() {
    return {
        name: "moodle-aliases",
        setup(build) {
            Object.entries(staticModuleRemaps).forEach(([specifier, target]) => {
                const filter = new RegExp(`^${escapeRegExp(specifier)}$`);
                build.onResolve({ filter }, args => {
                    const runtimePath = buildRelativeRuntimePath(args.importer, target);
                    return {
                        path: runtimePath,
                        external: true,
                    };
                });
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
