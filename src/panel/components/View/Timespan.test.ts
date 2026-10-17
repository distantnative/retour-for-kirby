import { describe, expect, it, vi } from "@test/panel";
import { mount as vueMount } from "@vue/test-utils";
import Timespan from "./Timespan.vue";

function timespan(props = {}) {
	return {
		from: "2026-10-01",
		to: "2026-10-31",
		unit: "month",
		isAll: false,
		isCurrent: true,
		prev: { from: "2026-09-01", to: "2026-09-30" },
		next: null,
		units: {
			all: { from: "2024-09-19", to: "2026-10-17" },
			year: { from: "2026-01-01", to: "2026-12-31" },
			month: { from: "2026-10-01", to: "2026-10-31" },
			week: { from: "2026-10-12", to: "2026-10-18" },
			day: { from: "2026-10-17", to: "2026-10-17" },
			today: { from: "2026-10-01", to: "2026-10-31" },
		},
		...props,
	};
}

function mount(props = {}) {
	const $panel = {
		dialog: { close: vi.fn(), open: vi.fn() },
		view: {
			path: "retour/failures",
			open: vi.fn(),
			query: { page: "3", searchterm: "wp" },
		},
	};

	const wrapper = vueMount(Timespan, {
		props: { timespan: timespan(props) },
		global: { mocks: { $panel } },
	});

	return Object.assign(wrapper, { $panel });
}

type Option = {
	click: () => void;
	current: boolean;
	icon: string;
	link: string | null;
	text: string;
};

// separators are plain strings
function dropdown(wrapper: ReturnType<typeof mount>) {
	return wrapper.vm.dropdown as unknown as Option[];
}

// `formatRange()` separates dates with thin spaces
function text(wrapper: ReturnType<typeof mount>) {
	return wrapper.vm.text.replace(/\s/g, " ");
}

describe("Timespan.vue", () => {
	describe("element", () => {
		it.rendersAs(() => mount(), "K-BUTTON-GROUP", "k-retour-timespan");
	});

	describe("text", () => {
		it.each([
			[
				{ unit: "day", from: "2026-10-17", to: "2026-10-17" },
				"October 17, 2026",
			],
			[
				{ unit: "week", from: "2026-10-12", to: "2026-10-18" },
				"Oct 12 – 18, 2026",
			],
			[{ unit: "month" }, "October 2026"],
			[{ unit: "year", from: "2026-01-01", to: "2026-12-31" }, "2026"],
			[
				{ unit: "days", from: "2025-12-28", to: "2026-01-03" },
				"Dec 28, 2025 – Jan 3, 2026",
			],
		])("names the %o as %s", (props, expected) => {
			expect(text(mount(props))).toBe(expected);
		});
	});

	describe("previous and next buttons", () => {
		it("link to the steps on the first page and keep the filter", () => {
			const [, prev, next] = mount().findAll("k-button");
			expect(prev.attributes("link")).toBe(
				"/retour/failures?searchterm=wp&from=2026-09-01&to=2026-09-30",
			);
			expect(prev.attributes("disabled")).toBe("false");
			expect(next.attributes("link")).toBeUndefined();
			expect(next.attributes("disabled")).toBe("true");
		});
	});

	describe("dropdown", () => {
		it("starts with all and a separator if the log has records", () => {
			const [all, separator, year] = dropdown(mount());
			expect(all.text).toBe("retour.stats.mode.all");
			expect(separator).toBe("-");
			expect(year.text).toBe("year");
		});

		it("leaves out all without records", () => {
			const units = { ...timespan().units, all: null };
			const [year] = dropdown(mount({ units }));
			expect(year.text).toBe("year");
		});

		it("lists the units, custom and today", () => {
			const texts = dropdown(mount()).map((option) => option.text ?? option);

			expect(texts).toEqual([
				"retour.stats.mode.all",
				"-",
				"year",
				"month",
				"retour.stats.mode.week",
				"day",
				"custom",
				"-",
				"today",
			]);
		});

		it("marks the current unit", () => {
			const options = dropdown(mount());
			expect(options[3]).toMatchObject({
				current: true,
				icon: "circle-focus",
				link: "/retour/failures?searchterm=wp&from=2026-10-01&to=2026-10-31",
			});
			expect(options[2]).toMatchObject({ current: false, icon: "circle" });
			expect(options[6].current).toBe(false);
		});

		it("marks a past unit without today", () => {
			expect(dropdown(mount({ isCurrent: false }))[3].icon).toBe(
				"circle-nested",
			);
		});

		it("marks all", () => {
			const options = dropdown(mount({ isAll: true, unit: "months" }));
			expect(options[0].current).toBe(true);
			expect(options[6].current).toBe(false);
		});

		it.each(["days", "months"])("marks custom for %s", (unit) => {
			expect(dropdown(mount({ unit }))[6].current).toBe(true);
		});

		it("opens the timespan dialog for custom", () => {
			const wrapper = mount();
			dropdown(wrapper)[6].click();
			expect(wrapper.$panel.dialog.open).toHaveBeenCalledWith(
				"retour/timespan",
				{ on: { submit: expect.any(Function) } },
			);
		});

		it("links the custom timespan on the first page and keeps the filter", () => {
			const wrapper = mount();
			dropdown(wrapper)[6].click();

			const [, { on }] = wrapper.$panel.dialog.open.mock.calls[0];
			on.submit({ from: "2026-09-28 00:00:00", to: "2026-10-04 00:00:00" });

			expect(wrapper.$panel.dialog.close).toHaveBeenCalled();
			expect(wrapper.$panel.view.open).toHaveBeenCalledWith(
				"/retour/failures?searchterm=wp&from=2026-09-28&to=2026-10-04",
			);
		});

		it("links today", () => {
			expect(dropdown(mount())[8]).toMatchObject({
				icon: "merge",
				link: "/retour/failures?searchterm=wp&from=2026-10-01&to=2026-10-31",
			});
		});
	});
});
