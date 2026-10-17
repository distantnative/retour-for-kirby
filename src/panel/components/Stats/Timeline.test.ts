import { afterEach, describe, expect, it, vi } from "@test/panel";
import { mount as vueMount } from "@vue/test-utils";
import Timeline from "./Timeline.vue";

function segments(labels: string[]) {
	return labels.map((label) => ({
		areas: [{ color: "blue", data: 1 }],
		label,
	}));
}

function mount(unit: string, labels: string[], now: string) {
	return vueMount(Timeline, {
		props: {
			data: segments(labels),
			timespan: { now, unit },
		},
		global: {
			mocks: {
				$panel: {
					observers: { resize: { observe: vi.fn(), unobserve: vi.fn() } },
					view: { path: "retour/redirects", query: {} },
				},
			},
		},
	});
}

const days = Array.from(
	{ length: 31 },
	(_, day) => `2026-10-${String(day + 1).padStart(2, "0")}`,
);

const hours = Array.from(
	{ length: 24 },
	(_, hour) => `2026-10-17 ${String(hour).padStart(2, "0")}`,
);

function current(wrapper: ReturnType<typeof mount>) {
	return wrapper.findAll("k-link").findIndex((link) => {
		return link.attributes("aria-current") === "date";
	});
}

describe("Timeline.vue", () => {
	afterEach(() => {
		vi.useRealTimers();
	});

	describe("now", () => {
		it("marks the server's day, not the browser's", () => {
			vi.useFakeTimers();
			vi.setSystemTime(new Date(2026, 9, 18, 3));

			const wrapper = mount("month", days, "2026-10-17 23:30:00");
			expect(current(wrapper)).toBe(16);
			expect(wrapper.vm.last).toBe(16);
		});

		it("marks the server's hour", () => {
			const wrapper = mount("day", hours, "2026-10-17 16:05:00");
			expect(current(wrapper)).toBe(16);
			expect(wrapper.vm.last).toBe(16);
		});

		it("marks nothing in a past timespan", () => {
			const wrapper = mount("month", days, "2026-11-02 10:00:00");
			expect(current(wrapper)).toBe(-1);
			expect(wrapper.vm.last).toBe(30);
		});
	});
});
