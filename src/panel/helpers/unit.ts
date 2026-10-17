/**
 * Returns the dayjs format for the timeline labels
 * of a timespan unit
 */
export function format(unit: string): string {
	const formats: Record<string, string> = {
		week: "ddd",
		month: "D",
		year: "MMM",
		months: "MMM YY",
	};

	return formats[unit] ?? "D MMM";
}

/**
 * Returns the unit of a single timeline segment
 * for a timespan unit
 */
export function subunit(unit: string): "day" | "hour" | "month" {
	const subunits: Record<string, "hour" | "month"> = {
		day: "hour",
		months: "month",
		year: "month",
	};

	return subunits[unit] ?? "day";
}
