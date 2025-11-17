import esbuild from "esbuild";
import {glob} from "glob";
import path from "path";
import fs from "fs";
import {createAliasPlugin} from "./.esbuild/aliases.mjs";
import {externalsPlugin} from "./.esbuild/externals.mjs";

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
        external: [],
        jsx: "automatic",
        minify: true,
        plugins: [ createAliasPlugin(), externalsPlugin ],
    }).catch(() => process.exit(1));
}
