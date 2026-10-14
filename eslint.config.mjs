import js from "@eslint/js";
import prettier from "eslint-config-prettier/flat";
import vue from "eslint-plugin-vue";
import globals from "globals";
import tseslint from "typescript-eslint";

export default [
	js.configs.recommended,
	...tseslint.configs.recommended.map((config) => ({
		...config,
		files: ["**/*.ts"],
	})),
	...vue.configs["flat/recommended"],
	prettier,
	{
		languageOptions: {
			globals: globals.browser,
		},
		rules: {
			"vue/attributes-order": "error",
			"vue/component-definition-name-casing": "off",
			"vue/html-closing-bracket-newline": [
				"error",
				{
					singleline: "never",
					multiline: "always",
				},
			],
			"vue/multi-word-component-names": "off",
			"vue/require-default-prop": "off",
			"vue/require-prop-types": "error",
		},
	},
];
