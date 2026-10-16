/**
 * Returns how many days a weekday (0 = Sunday … 6 = Saturday)
 * lies after the first day of the week
 */
export function daysIntoWeek(day: number, firstWeekday: number): number {
	return (day - firstWeekday + 7) % 7;
}

/**
 * Returns the dayjs format for the timeline labels
 * of a timespan unit
 */
export function format(unit: string): string {
	const formats: Record<string, string> = {
		day: "HH",
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
		year: "month",
	};

	return subunits[unit] ?? "day";
}
