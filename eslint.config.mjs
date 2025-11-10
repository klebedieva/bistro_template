import js from "@eslint/js";
import globals from "globals";
import pluginImport from "eslint-plugin-import";
import pluginPromise from "eslint-plugin-promise";
import pluginJsdoc from "eslint-plugin-jsdoc";
import pluginPrettier from "eslint-plugin-prettier";
import { defineConfig } from "eslint/config";

export default defineConfig([
  {
    ignores: [
      "node_modules/**",
      "vendor/**",
      "var/**",
      "public/assets/**",
      "public/build/**",
      "public/static/vendor/**",
      "assets/vendor/**"
    ],
  },
  {
    ...js.configs.recommended,
    files: ["**/*.{js,mjs,cjs}"],
    languageOptions: {
      ...js.configs.recommended.languageOptions,
      globals: {
        ...globals.browser,
        ...globals.node,
      },
      sourceType: "module",
    },
    plugins: {
      import: pluginImport,
      promise: pluginPromise,
      jsdoc: pluginJsdoc,
      prettier: pluginPrettier,
    },
    rules: {
      ...js.configs.recommended.rules,
      "prettier/prettier": "error",
    },
  },
  {
    files: ["**/*.config.{js,cjs,mjs}", "eslint.config.mjs"],
    languageOptions: {
      globals: {
        ...globals.node,
      },
      sourceType: "module",
    },
  },
]);
