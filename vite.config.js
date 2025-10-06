import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";
import { globSync } from "glob";
import path from "path";
import fs from "fs-extra";

// --- Step 1: Find only React entry files ---
const reactEntries = globSync("**/react/src/**/*.{js,jsx,ts,tsx}", {
  ignore: ["node_modules/**", "**/build/**", "vite_build/**"],
});

console.log("Found React entry files:", reactEntries);

const input = {};
reactEntries.forEach((file) => {
  const pluginName = path.basename(path.dirname(path.dirname(file))); // parent folder of react
  const baseName = path.basename(file, path.extname(file));
  const entryKey = `${pluginName}/${baseName}`;
  input[entryKey] = path.resolve(file);
});

console.log("Rollup input keys:", Object.keys(input));

// --- Step 2: Copy plugin build files ---
function copyToPluginBuild() {
  return {
    name: "copy-to-plugin-build",
    writeBundle(options, bundle) {
      Object.values(bundle).forEach((chunk) => {
        if (!chunk.facadeModuleId) return; // skip non-entry/chunk files

        const srcFile = chunk.facadeModuleId;
        if (!srcFile.includes("/react/src/")) return; // only react code

        // build folder inside the plugin
        const pluginBuildDir = path.join(
          srcFile.split("/react/src/")[0],
          "react/build"
        );
        fs.ensureDirSync(pluginBuildDir);
        fs.copyFileSync(
          path.join(options.dir || "vite_build", chunk.fileName),
          path.join(pluginBuildDir, path.basename(chunk.fileName))
        );
      });
    },
  };
}

// --- Step 3: Fix chunk imports in entry files ---
function fixChunkImports() {
  return {
    name: "fix-chunk-imports",
    generateBundle(_, bundle) {
      for (const [fileName, chunk] of Object.entries(bundle)) {
        if (chunk.type === "chunk" && chunk.isEntry) {
          // Replace ../../../chunks/... with ./chunks/...
          chunk.code = chunk.code.replace(
            /((?:\.\.\/)+)chunks\//g,
            "./chunks/"
          );
        }
      }
    },
  };
}

// --- Step 4: Vite config ---
export default defineConfig({
    root: process.cwd(),
    publicDir: false,
    plugins: [react(), copyToPluginBuild(), fixChunkImports()],
    build: {
        outDir: "vite_build",
        emptyOutDir: true,
        rollupOptions: {
              input,
              output: {
                entryFileNames: "[name].js",        // flattened per plugin
                chunkFileNames: "chunks/[name]-[hash].js", // shared React chunk
                assetFileNames: "assets/[name]-[hash][extname]",
                manualChunks: { react: ["react", "react-dom"] },
              },
        },
    },
    optimizeDeps: {
        include: ["react", "react-dom"],
    },
});
