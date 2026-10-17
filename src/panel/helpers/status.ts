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
