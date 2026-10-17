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
 * shortened in the given locale
 *
 * @example
 * axis(5000, "en") // => ["1K", "2K", "3K", "4K", "5K"]
 */
export function axis(max: number, locale?: string): string[] {
	const span = max / 5;
	const formatter = new Intl.NumberFormat(locale, {
		notation: "compact",
		// would otherwise round to two significant digits (1.3K)
		maximumFractionDigits: 2,
	});

	return [1, 2, 3, 4, 5].map((x) => formatter.format(x * span));
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
 * Returns the top of the y-axis: the highest stack
 * plus some headroom, rounded to a quarter of its
 * order of magnitude, or at least to a multiple of 5
 * so that all five ticks are whole numbers
 *
 * @example
 * max([{ label: "2025-01", areas: [{ color: "red", data: 900 }] }]) // => 1000
 */
export function max(segments: Segment[]): number {
	const highest = Math.max(...segments.map((segment) => sum(segment.areas)));

	if (highest > 0) {
		const digits = highest.toString().length;
		const step = Math.max(Math.pow(10, digits) / 4, 5);
		return Math.ceil((highest * 1.1) / step) * step;
	}

	return 5;
}

/**
 * Returns the clip path of a stacked area across all segments;
 * it ends at the last segment, later ones (and any further
 * points, up to `points`) collapse onto it
 *
 * @example
 * polygon([[{ path: 5, mask: 0 }], [{ path: 10, mask: 0 }]], 0, 10)
 * // => "polygon(0% 50%, 100% 0%, 100% 100%, 0% 100%)"
 */
export function polygon(
	bounds: Bound[][],
	area: number,
	max: number,
	last: number = bounds.length - 1,
	points: number = bounds.length,
): string {
	const width = Math.max(bounds.length - 1, 1);
	const point = (segment: number, key: keyof Bound): string => {
		const x = Math.min(segment, last);
		const bound = bounds[x][area];
		const y = segment > last ? bound.mask : bound[key];
		return `${(100 * x) / width}% ${100 - (100 * y) / max}%`;
	};

	const segments = [...Array(points).keys()];
	const top = segments.map((segment) => point(segment, "path"));
	const bottom = segments.reverse().map((segment) => point(segment, "mask"));

	return `polygon(${[...top, ...bottom].join(", ")})`;
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

export function sum(items: { data: number }[]): number {
	return items.reduce((total, item) => total + item.data, 0);
}
