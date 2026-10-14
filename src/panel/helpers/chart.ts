export type Area = {
	color: string;
	data: number;
};

export type Bound = {
	mask: number;
	path: number;
};

export type Segment = {
	areas: Area[];
	label: string;
};

/**
 * Returns the five labels of the y-axis,
 * shortening thousands and millions
 *
 * @example
 * axis(5000) // => ["1k", "2k", "3k", "4k", "5k"]
 */
export function axis(max: number): string[] {
	const span = max / 5;

	return [1, 2, 3, 4, 5].map((x) => {
		const tick = x * span;

		if (tick >= 1000000) {
			return Math.floor(tick / 100000) / 10 + "M";
		}

		if (tick >= 1000) {
			return Math.floor(tick / 100) / 10 + "k";
		}

		return String(tick);
	});
}

/**
 * Returns where each stacked area starts (mask)
 * and ends (path) for every segment
 */
export function bounds(segments: Segment[]): Bound[][] {
	return segments.map((segment) =>
		segment.areas.map((area, index) => {
			const stack = sum(segment.areas.slice(0, index));

			return {
				path: stack + area.data,
				mask: stack,
			};
		}),
	);
}

/**
 * Returns the CSS variables to clip an area
 * between the current and the next segment
 */
export function clip(
	current: Bound,
	next: Bound | undefined,
	max: number,
): string {
	next ??= { path: 0, mask: 0 };

	return [
		`--p0: ${current.path / max}`,
		`--p1: ${next.path / max}`,
		`--m0: ${current.mask / max}`,
		`--m1: ${next.mask / max}`,
	].join("; ");
}

/**
 * Returns the top of the y-axis: the highest stack
 * plus some headroom, rounded to a quarter of its
 * order of magnitude
 *
 * @example
 * max([{ label: "2025-01", areas: [{ color: "red", data: 900 }] }]) // => 1000
 */
export function max(segments: Segment[]): number {
	const highest = Math.max(...segments.map((segment) => sum(segment.areas)));

	if (highest > 0) {
		const digits = highest.toString().length;
		const step = Math.pow(10, digits) / 4;
		return Math.ceil((highest * 1.1) / step) * step;
	}

	return 5;
}

/**
 * Returns the CSS variables for the colors and angles
 * of the three slices of the half pie chart
 */
export function pie(slices: Area[]): Record<string, string> {
	const total = sum(slices);

	if (total === 0) {
		return {
			"--pie-c0": "transparent",
			"--pie-c1": "transparent",
			"--pie-c2": "transparent",
			"--pie-a0": "0deg",
			"--pie-a1": "0deg",
		};
	}

	const deg = total / 180;
	const a0 = slices[0].data / deg;
	const a1 = a0 + slices[1].data / deg;

	return {
		"--pie-c0": slices[0].color,
		"--pie-c1": slices[1].color,
		"--pie-c2": slices[2].color,
		"--pie-a0": `${a0}deg`,
		"--pie-a1": `${a1}deg`,
	};
}

/**
 * Returns the sum of all data values
 */
export function sum(items: { data: number }[]): number {
	return items.reduce((total, item) => total + item.data, 0);
}
