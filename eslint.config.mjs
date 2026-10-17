import js from "@eslint/js";
import prettier from "eslint-config-prettier/flat";
import vue from "eslint-plugin-vue";
import a11y from "eslint-plugin-vuejs-accessibility";
import globals from "globals";
import tseslint from "typescript-eslint";

export default [
	js.configs.recommended,
	...tseslint.configs.recommended.map((config) => ({
		...config,
		files: ["**/*.ts"],
	})),
	...vue.configs["flat/recommended"],
	...a11y.configs["flat/recommended"],
	prettier,
	{
		languageOptions: {
			globals: globals.browser,
		},
		rules: {
			"vue/attributes-order": "error",
			"vue/component-definition-name-casing": "off",
			"vue/multi-word-component-names": "off",
			"vue/require-default-prop": "off",
			"vue/require-prop-types": "error",
			// the filter input only appears, and then focuses
			// itself, after a click on the filter button
			"vuejs-accessibility/no-autofocus": "off",
		},
	},
	{
		files: ["**/*.test.ts"],
		rules: {
			"vue/one-component-per-file": "off",
		},
	},
];
