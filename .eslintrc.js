module.exports = {
    overrides: [
        {
            files: ["public/**/react/src/**/*.{ts,tsx}"],
            parser: "@typescript-eslint/parser",
            parserOptions: {
                sourceType: "module",
                ecmaVersion: "latest",
                ecmaFeatures: { jsx: true }
            },
            plugins: ["@typescript-eslint"],
            extends: ["plugin:@typescript-eslint/recommended"]
        }
    ]
};
