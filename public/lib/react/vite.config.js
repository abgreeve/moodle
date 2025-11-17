import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";
import path from "path";

const reactRoot = __dirname;

export default defineConfig((mode) => {
  const isDev = mode.mode === "development";
  return {
    root: reactRoot,
    publicDir: false,
    plugins: [react()],
    define: {
      "process.env": "{}",
      "process.env.NODE_ENV": JSON.stringify(isDev ? "development" : "production"),
      global: "window",
    },
    build: {
      lib: {
        entry: {
          core: path.resolve(reactRoot, "src/main.ts"),
        },
        name: "ReactApp",
        formats: ["iife"],
      },
      rollupOptions: {
        output: {
          entryFileNames: "[name].[format].js",
        },
      },
      outDir: path.resolve(reactRoot, "build"),
      emptyOutDir: true,
    }
  }
});
