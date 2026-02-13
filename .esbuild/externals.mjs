export const externalsPlugin = {
    name: "externals",
    setup(build) {
        // Everything else should be bundled by esbuild and fail fast if missing.
        build.onResolve({ filter: /^[^./].*/ }, (args) => {
            const path = args.path;

            if (
                path === "@moodle/lms" ||
                path.startsWith("@moodle/lms/") ||
                path === "react" ||
                path.startsWith("react/") ||
                path === "react-dom" ||
                path.startsWith("react-dom/") ||
                path === "@moodlehq/design-system"
            ) {
                return { path, external: true };
            }

            return;
        });
    }
};
