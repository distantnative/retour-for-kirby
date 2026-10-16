import { describe, expect, it } from "vitest";
import { daysIntoWeek, format, subunit } from "./unit";

describe("unit.daysIntoWeek()", () => {
	const cases: { day: number; first: number; expected: number }[] = [
		{ day: 0, first: 0, expected: 0 },
		{ day: 6, first: 0, expected: 6 },
		{ day: 1, first: 1, expected: 0 },
		{ day: 3, first: 1, expected: 2 },
		{ day: 0, first: 1, expected: 6 },
		{ day: 6, first: 6, expected: 0 },
		{ day: 5, first: 6, expected: 6 },
	];

	it.each(cases)(
		"returns $expected for day $day with weeks starting on $first",
		({ day, first, expected }) => {
			expect(daysIntoWeek(day, first)).toBe(expected);
		},
	);
});

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
