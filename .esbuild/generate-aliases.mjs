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
 * Alias generation.
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import fs from "fs";
import path from "path";
import { createRequire } from "module";

const rootDir = process.cwd();
const esbuildDir = path.join(rootDir, ".esbuild");

const tsconfigOut = path.join(rootDir, "tsconfig.aliases.json");

/**
 * Load Moodle component paths from `.grunt/components.js`.
 *
 * @returns {Record<string, string>} Map of component paths to component names.
 */
function loadComponentPathMap() {
    const require = createRequire(import.meta.url);

    // Load Moodle's components data from .grunt/components.js.
    const { fetchComponentData } = require(
        path.join(process.cwd(), ".grunt", "components.js")
    );

    // Format: `{ components: { "public/lib": "core", ... } }`.
    const componentsData = fetchComponentData().components;

    // Expected:
    //   `{ "public/lib": "core", "public/ai": "core_ai", ... }`.
    return componentsData;
}

/**
 * Check whether a directory contains at least one `.tsx` file recursively.
 *
 * @param {string} dir Directory path to inspect.
 * @returns {boolean} True when at least one `.tsx` file is found.
 */
function hasTsx(dir) {
    if (!fs.existsSync(dir)) {
        return false;
    }

    const entries = fs.readdirSync(dir, { withFileTypes: true });

    for (const entry of entries) {
        const full = path.join(dir, entry.name);

        if (entry.isDirectory()) {
        if (hasTsx(full)) {
            return true;
        }
        } else if (entry.isFile()) {
        if (entry.name.endsWith(".tsx")) {
            return true;
        }
        }
    }

    return false;
}

/**
 * Compare two TypeScript `paths` maps for exact equality.
 *
 * @param {Record<string, string[]>} a First paths map.
 * @param {Record<string, string[]>} b Second paths map.
 * @returns {boolean} True when keys and values match in order.
 */
function pathsEqual(a, b) {
    const aKeys = Object.keys(a);
    const bKeys = Object.keys(b);
    if (aKeys.length !== bKeys.length) {
        return false;
    }

    for (const key of aKeys) {
        if (!Object.prototype.hasOwnProperty.call(b, key)) {
        return false;
        }

        const aArr = a[key] ?? [];
        const bArr = b[key] ?? [];
        if (aArr.length !== bArr.length) {
        return false;
        }

        for (let i = 0; i < aArr.length; i++) {
        if (aArr[i] !== bArr[i]) {
            return false;
        }
        }
    }

    return true;
}

/**
 * Generate `tsconfig.aliases.json` from Moodle component metadata.
 *
 * Includes core aliases, discovers plugin React source aliases, and skips
 * rewriting when aliases have not changed.
 *
 * @returns {void}
 */
export function generateAliases() {
    const componentPathMap = loadComponentPathMap();

    /** @type {Record<string, string>} */
    const globalAliasMap = {};

    // Build alias map from components that actually have react/src/*.tsx
    for (const [componentPath, componentName] of Object.entries(componentPathMap)) {
        // Example:
        //   componentPath: "public/lib"
        //   componentName: "core"

        const reactSrcDir = path.join(rootDir, componentPath, "js", "react", "src");

        // Skip any component that doesn't have React TSX code
        if (!hasTsx(reactSrcDir)) {
        continue;
        }

        // Runtime alias key format: @moodle/lms/<componentName>/*
        // Used with import maps and reactscript.php.
        const runtimeAliasKey = `@moodle/lms/${componentName}/*`;

        // Target pattern: <componentPath>/react/src/*
        // Example:
        //   public/lib/react/src/*
        //   public/ai/js/react/src/*
        const targetPattern = path
        .join(componentPath, "js", "react", "src", "*")
        .replace(/\\/g, "/");

        globalAliasMap[runtimeAliasKey] = targetPattern;
    }

    // Build TS paths for tsconfig.aliases.json
    const tsPaths = {};
    tsPaths["@moodle/lms/core/*"] = ["public/lib/js/react/src/*"]; // Always include core alias.
    tsPaths["@moodle/lms/core/react_autoinit"] = ["public/lib/react_autoinit/src/index.ts"];
    for (const [alias, target] of Object.entries(globalAliasMap)) {
        tsPaths[alias] = [target];
    }


    const tsconfig = {
        compilerOptions: { paths: tsPaths },
    };

    // If tsconfig paths didn't change, skip regeneration
    let previousPaths = null;
    if (fs.existsSync(tsconfigOut)) {
        try {
        const previousTsconfig = JSON.parse(fs.readFileSync(tsconfigOut, "utf8"));
        previousPaths = previousTsconfig.compilerOptions?.paths ?? {};
        } catch {
        // ignore parse errors and treat as "no previous cache"
        previousPaths = null;
        }
    }

    if (previousPaths && pathsEqual(previousPaths, tsPaths)) {
        console.log("No alias changes detected, skipping tsconfig.aliases.json regeneration.");
        return;
    }

    /**
     * Convert JSON output so single-value arrays are rendered on one line.
     *
     * @param {unknown} obj Data to stringify.
     * @returns {string} JSON string with flattened single-value arrays.
     */
    function stringifyFlatArrays(obj) {
        const json = JSON.stringify(obj, null, 2);
        return json.replace(/\[\s+\"(.*?)\"\s+\]/g, '["$1"]');
    }

    // Ensure .esbuild directory exists (even though we don't store aliases there now,
    // other build scripts may still rely on .esbuild existing).
    if (!fs.existsSync(esbuildDir)) {
        fs.mkdirSync(esbuildDir, { recursive: true });
    }

    // Write tsconfig.aliases.json
    fs.writeFileSync(tsconfigOut, stringifyFlatArrays(tsconfig));
    console.log("Generated TS alias file:", tsconfigOut);
}
