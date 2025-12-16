const INTERNAL_ALIASES = ["@moodle/"];

export const externalsPlugin = {
    name: "externals",
    setup(build) {
        build.onResolve({ filter: /^[^./].*/ }, args => {
            if (INTERNAL_ALIASES.some(prefix => args.path.startsWith(prefix))) {
                return;
            }

            return { path: args.path, external: true };
        });
    }
};
