import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";
import { globSync } from "glob";
import path from "path";
import fs from "fs-extra";

const rootDir = process.cwd();
const publicRoot = path.resolve(rootDir, "public");
const buildRoot = path.resolve(publicRoot, "react_build");

const entryPattern = "public/**/react/src/{main,index}.{js,jsx,ts,tsx}";
const ignorePatterns = [
  "**/node_modules/**",
  "**/public/react_build/**",
  "**/vite_build/**",
];

const reactSegment = "/react/src/";

const toPosix = (value) => value.split(path.sep).join(path.posix.sep);
const joinPosix = (...parts) => parts.filter(Boolean).join("/");

function collectReactEntries() {
  const entries = globSync(entryPattern, { ignore: ignorePatterns });

  return entries
    .map((entryPath) => {
      const absoluteEntry = path.resolve(rootDir, entryPath);
      const relativeToPublic = toPosix(
        path.relative(publicRoot, absoluteEntry)
      );
      const segmentIndex = relativeToPublic.indexOf(reactSegment);
      if (segmentIndex === -1) {
        return null;
      }

      const appPath = relativeToPublic.slice(0, segmentIndex);
      const relativeWithinSrc = relativeToPublic.slice(
        segmentIndex + reactSegment.length
      );
      if (!relativeWithinSrc) {
        return null;
      }

      const parsed = path.posix.parse(relativeWithinSrc);
      const entryKey = joinPosix(parsed.dir, parsed.name);

      return {
        absoluteEntry,
        appPath,
        relativeDir: parsed.dir,
        baseName: parsed.name,
        entryKey,
      };
    })
    .filter(Boolean);
}

function groupEntriesByApp(definitions) {
  return definitions.reduce((map, definition) => {
    const key = definition.appPath;
    if (!map.has(key)) {
      map.set(key, []);
    }
    map.get(key).push(definition);
    return map;
  }, new Map());
}

function createEmptyBuildConfig() {
  return {
    root: rootDir,
    publicDir: false,
    plugins: [react()],
    build: {
      outDir: buildRoot,
      emptyOutDir: true,
      cssCodeSplit: false,
      rollupOptions: {
        input: {},
      },
    },
    optimizeDeps: {
      include: ["react", "react-dom"],
    },
  };
}

function createAppBuildConfig(appPath, entries) {
  const input = {};

  entries
    .sort((left, right) => left.entryKey.localeCompare(right.entryKey))
    .forEach((entry) => {
      const alias = joinPosix(entry.relativeDir, entry.baseName) || entry.baseName;
      input[alias] = entry.absoluteEntry;
    });

  const outDir = appPath ? path.join(buildRoot, appPath) : buildRoot;

  return {
    root: rootDir,
    publicDir: false,
    plugins: [react()],
    build: {
      outDir,
      emptyOutDir: true,
      cssCodeSplit: false,
      rollupOptions: {
        input,
        output: {
          entryFileNames: "[name].js",
          chunkFileNames: "chunks/[name]-[hash].js",
          assetFileNames: "assets/[name]-[hash][extname]",
        },
      },
    },
    optimizeDeps: {
      include: ["react", "react-dom"],
    },
  };
}

const isBuildCommand = process.argv.includes("build");
const definitions = collectReactEntries();
const grouped = groupEntriesByApp(definitions);

let finalConfig;

if (isBuildCommand) {
  if (definitions.length > 0) {
    fs.ensureDirSync(buildRoot);
    fs.emptyDirSync(buildRoot);
  }

  const buildConfigs = grouped.size
    ? Array.from(grouped.entries()).map(([appPath, entries]) =>
        createAppBuildConfig(appPath, entries)
      )
    : [createEmptyBuildConfig()];

  finalConfig =
    buildConfigs.length === 1 ? buildConfigs[0] : buildConfigs;
} else {
  finalConfig = {
    root: rootDir,
    publicDir: false,
    plugins: [react()],
    optimizeDeps: {
      include: ["react", "react-dom"],
    },
  };
}

export default defineConfig(finalConfig);
