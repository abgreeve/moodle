import esbuild from "esbuild";
import {glob} from "glob";
import path from "path";
import fs from "fs";
import {createAliasPlugin} from "./.esbuild/aliases.mjs";
import {externalsPlugin} from "./.esbuild/externals.mjs";

// Build the shared React runtime files.
const sharedReactEntries = [
    {
        entry: "public/lib/react/src/react.ts",
        outfile: "public/lib/react/build/react.js",
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

sharedReactEntries.forEach(({entry, outfile}) => {
    esbuild.build({
        entryPoints: [entry],
        bundle: true,
        format: "esm",
        outfile,
        minify: true,
    });
});

// Build special case files.
esbuild.build({
    entryPoints: ["public/lib/react_autoinit/src/index.ts"],
    bundle: true,
    format: "esm",
    outfile: "public/lib/react_autoinit/build/index.js",
    external: ["react", "react-dom", "react-dom/client"],
});

const entryPoints = glob.sync("public/**/react/src/**/*.tsx");
console.log(entryPoints);

for (const entry of entryPoints) {
    const part = entry.split("/react/src/")[0];
    const file = entry.split("/react/src/")[1];

    const output = path.join(part, "react", "build", file.replace(/\.tsx$/, ".js"));

    fs.mkdirSync(path.dirname(output), { recursive: true });

    console.log(`Building: ${entry} -> ${output}`);

    esbuild.build({
        entryPoints: [entry],
        bundle: true,
        outfile: output,
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
        minify: true,
        sourcemap: true,
        plugins: [ createAliasPlugin(), externalsPlugin ],
    }).catch(() => process.exit(1));
}
