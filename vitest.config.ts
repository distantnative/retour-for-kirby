import { fileURLToPath } from "node:url";
import vue from "@vitejs/plugin-vue";
import { defineConfig } from "vitest/config";

export default defineConfig({
	plugins: [vue()],
	resolve: {
		alias: {
			"@test": fileURLToPath(new URL("./tests", import.meta.url)),
		},
	},
	test: {
		css: false,
		environment: "happy-dom",
		include: ["src/panel/**/*.test.ts"],
		setupFiles: ["tests/panel/setup.ts"],
	},
});
