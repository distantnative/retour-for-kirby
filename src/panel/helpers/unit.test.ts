import { describe, expect, it } from "vitest";
import { format, subunit } from "./unit";

describe("unit.format()", () => {
	const cases: { unit: string; expected: string }[] = [
		{ unit: "week", expected: "ddd" },
		{ unit: "month", expected: "D" },
		{ unit: "year", expected: "MMM" },
		{ unit: "months", expected: "MMM YY" },
		{ unit: "days", expected: "D MMM" },
	];

	it.each(cases)("returns the format for $unit", ({ unit, expected }) => {
		expect(format(unit)).toBe(expected);
	});
});

describe("unit.subunit()", () => {
	const cases: { unit: string; expected: string }[] = [
		{ unit: "day", expected: "hour" },
		{ unit: "year", expected: "month" },
		{ unit: "month", expected: "day" },
		{ unit: "months", expected: "month" },
		{ unit: "days", expected: "day" },
	];

	it.each(cases)("returns the subunit for $unit", ({ unit, expected }) => {
		expect(subunit(unit)).toBe(expected);
	});
});
