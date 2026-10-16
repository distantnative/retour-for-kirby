import { describe, expect, it } from "vitest";
import { axis, bounds, clip, max, pie, sum } from "./chart";
import type { Segment } from "./chart";

const segment = (...data: number[]): Segment => ({
	label: "2026-01-01",
	areas: data.map((value) => ({ color: "red", data: value })),
});

describe("chart.axis()", () => {
	const cases: { max: number; expected: string[] }[] = [
		{ max: 5, expected: ["1", "2", "3", "4", "5"] },
		{ max: 75, expected: ["15", "30", "45", "60", "75"] },
		{ max: 5000, expected: ["1K", "2K", "3K", "4K", "5K"] },
		{ max: 7500, expected: ["1.5K", "3K", "4.5K", "6K", "7.5K"] },
		{ max: 1250, expected: ["250", "500", "750", "1K", "1.25K"] },
		{ max: 2500000, expected: ["500K", "1M", "1.5M", "2M", "2.5M"] },
		{ max: 1250000, expected: ["250K", "500K", "750K", "1M", "1.25M"] },
	];

	it.each(cases)("returns ticks for $max", ({ max, expected }) => {
		expect(axis(max, "en")).toStrictEqual(expected);
	});

	it("formats ticks in the given locale", () => {
		// German doesn't shorten thousands
		expect(axis(1250, "de")).toStrictEqual([
			"250",
			"500",
			"750",
			"1000",
			"1250",
		]);
		expect(axis(1250000, "de")).toStrictEqual([
			"250.000",
			"500.000",
			"750.000",
			"1\u00a0Mio.",
			"1,25\u00a0Mio.",
		]);
	});
});

describe("chart.bounds()", () => {
	it("stacks the areas of each segment", () => {
		expect(bounds([segment(1, 2, 3), segment(0, 5, 0)])).toStrictEqual([
			[
				{ path: 1, mask: 0 },
				{ path: 3, mask: 1 },
				{ path: 6, mask: 3 },
			],
			[
				{ path: 0, mask: 0 },
				{ path: 5, mask: 0 },
				{ path: 5, mask: 5 },
			],
		]);
	});

	it("returns an empty array without segments", () => {
		expect(bounds([])).toStrictEqual([]);
	});
});

describe("chart.clip()", () => {
	it("scales the bounds of both segments", () => {
		expect(clip({ path: 5, mask: 2 }, { path: 10, mask: 0 }, 10)).toBe(
			"--p0: 0.5; --p1: 1; --m0: 0.2; --m1: 0",
		);
	});

	it("drops to zero without a next segment", () => {
		expect(clip({ path: 5, mask: 2 }, undefined, 10)).toBe(
			"--p0: 0.5; --p1: 0; --m0: 0.2; --m1: 0",
		);
	});
});

describe("chart.max()", () => {
	const cases: { name: string; segments: Segment[]; expected: number }[] = [
		{ name: "falls back without segments", segments: [], expected: 5 },
		{ name: "falls back without data", segments: [segment(0, 0)], expected: 5 },
		{ name: "rounds single digits", segments: [segment(3)], expected: 5 },
		{ name: "rounds low single digits", segments: [segment(1)], expected: 5 },
		{ name: "rounds high single digits", segments: [segment(5)], expected: 10 },
		{ name: "rounds double digits", segments: [segment(50)], expected: 75 },
		{
			name: "uses the highest stack",
			segments: [segment(100, 200), segment(400, 500)],
			expected: 1000,
		},
	];

	it.each(cases)("$name", ({ segments, expected }) => {
		expect(max(segments)).toBe(expected);
	});

	it("divides the axis into whole numbers", () => {
		for (let highest = 1; highest <= 1000; highest++) {
			expect(max([segment(highest)]) % 5).toBe(0);
		}
	});
});

describe("chart.pie()", () => {
	it("returns transparent slices without data", () => {
		expect(
			pie([
				{ color: "blue", data: 0 },
				{ color: "gray", data: 0 },
				{ color: "red", data: 0 },
			]),
		).toStrictEqual({
			"--pie-c0": "transparent",
			"--pie-c1": "transparent",
			"--pie-c2": "transparent",
			"--pie-a0": "0deg",
			"--pie-a1": "0deg",
		});
	});

	it("splits the half circle by data", () => {
		expect(
			pie([
				{ color: "blue", data: 90 },
				{ color: "gray", data: 45 },
				{ color: "red", data: 45 },
			]),
		).toStrictEqual({
			"--pie-c0": "blue",
			"--pie-c1": "gray",
			"--pie-c2": "red",
			"--pie-a0": "90deg",
			"--pie-a1": "135deg",
		});
	});
});

describe("chart.sum()", () => {
	it("adds up all data", () => {
		expect(sum([{ data: 1 }, { data: 2 }, { data: 3 }])).toBe(6);
	});

	it("returns zero without items", () => {
		expect(sum([])).toBe(0);
	});
});
