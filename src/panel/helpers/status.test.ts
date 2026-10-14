import { describe, expect, it } from "vitest";
import { color } from "./status";

describe("status.color()", () => {
	const cases: {
		status: number | string | null | undefined;
		expected: string;
	}[] = [
		{ status: undefined, expected: "var(--color-gray-400)" },
		{ status: null, expected: "var(--color-gray-400)" },
		{ status: "", expected: "var(--color-gray-400)" },
		{ status: 200, expected: "var(--color-blue-500)" },
		{ status: 301, expected: "var(--color-green-500)" },
		{ status: "302", expected: "var(--color-green-500)" },
		{ status: 404, expected: "var(--color-red-500)" },
		{ status: "500", expected: "var(--color-red-500)" },
	];

	it.each(cases)("returns the color for $status", ({ status, expected }) => {
		expect(color(status)).toBe(expected);
	});
});
