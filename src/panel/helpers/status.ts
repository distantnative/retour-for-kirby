/**
 * Returns the color for an HTTP status code:
 * gray without a code, blue for 1xx/2xx,
 * green for redirects and red for errors
 *
 * @example
 * color(301) // => "var(--color-green-500)"
 * color("404") // => "var(--color-red-500)"
 */
export function color(status?: number | string | null): string {
	const code = parseInt(String(status));

	if (code >= 400) {
		return "var(--color-red-500)";
	}

	if (code >= 300) {
		return "var(--color-green-500)";
	}

	if (code >= 100) {
		return "var(--color-blue-500)";
	}

	return "var(--color-gray-400)";
}
