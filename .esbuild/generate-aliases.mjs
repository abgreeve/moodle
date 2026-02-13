import fs from "fs";
import path from "path";
import { createRequire } from "module";

const rootDir = process.cwd();
const esbuildDir = path.join(rootDir, ".esbuild");

const tsconfigOut = path.join(rootDir, "tsconfig.aliases.json");

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

// Check whether a react/src directory has any .tsx file recursively.
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

// Check whether two path maps are equal.
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

    // Collapse ["value"] onto one line
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
