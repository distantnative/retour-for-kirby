import { describe, expect, it, vi } from "@test/panel";
import { mount as vueMount } from "@vue/test-utils";
import { defineComponent, h } from "vue";
import CollectionView from "./CollectionView.vue";

// renders the buttons slot with the filter, unlike an unresolved component
const RetourView = defineComponent({
	setup(props, { slots }) {
		return () => h("k-retour-view", [slots.buttons?.(), slots.default?.()]);
	},
});

// emits values like the real input
const Input = defineComponent({
	emits: ["input"],
	setup() {
		return () => h("k-input");
	},
});

function mount(props = {}, defaultSortBy: string | null = null) {
	const $panel = { reload: vi.fn(() => Promise.resolve()) };

	const wrapper = vueMount(
		defineComponent({
			extends: CollectionView,
			computed: { defaultSortBy: () => defaultSortBy },
		}),
		{
			props: {
				data: [{ path: "a" }, { path: "b" }],
				pagination: { limit: 50, offset: 50, page: 2, total: 52 },
				...props,
			},
			global: {
				mocks: {
					$helper: { debounce: (fn: () => void) => fn },
					$panel,
				},
				stubs: { "k-input": Input, "k-retour-view": RetourView },
			},
		},
	);

	return Object.assign(wrapper, { $panel });
}

describe("CollectionView.vue", () => {
	describe("element", () => {
		it.rendersAs(() => mount(), "K-RETOUR-VIEW", "k-retour-collection-view");
	});

	describe("table", () => {
		it("numbers the rows of the page", () => {
			expect(mount().find("k-table").attributes("index")).toBe("51");
		});

		it("shows the empty state without rows", () => {
			const wrapper = mount({ data: [] });
			expect(wrapper.find("k-table").exists()).toBe(false);
			expect(wrapper.find("k-empty").exists()).toBe(true);
		});

		it("shows no results while filtering", () => {
			const wrapper = mount({ data: [], searchterm: "wp" });
			expect(wrapper.find("k-empty").attributes()).toMatchObject({
				icon: "search",
				text: "search.results.none",
			});
		});
	});

	describe("pagination", () => {
		it("reloads the page", async () => {
			const wrapper = mount();
			await wrapper.find("k-pagination").trigger("paginate", { page: 3 });
			expect(wrapper.$panel.reload).toHaveBeenCalledWith({
				query: { page: 3 },
			});
		});
	});

	describe("sorting", () => {
		it.each([
			[null, "path asc"],
			["path asc", "path desc"],
			["path desc", null],
			["hits asc", "path asc"],
		])("goes from %s to %s without a default", (sortBy, next) => {
			const wrapper = mount({ sortBy });
			wrapper.vm.onHeader({ columnIndex: "path" });
			expect(wrapper.$panel.reload).toHaveBeenCalledWith({
				query: { page: null, sortBy: next },
			});
		});

		it.each([
			[null, "hits asc"],
			["hits asc", null],
		])("goes from %s to %s for the default column", (sortBy, next) => {
			const wrapper = mount({ sortBy }, "hits desc");
			wrapper.vm.onHeader({ columnIndex: "hits" });
			expect(wrapper.$panel.reload).toHaveBeenCalledWith({
				query: { page: null, sortBy: next },
			});
		});

		it("splits the order into column and direction", () => {
			expect(mount({ sortBy: "path desc" }).vm.sorting).toEqual([
				"path",
				"desc",
			]);
			expect(mount({}, "hits desc").vm.sorting).toEqual(["hits", "desc"]);
		});
	});

	describe("reload()", () => {
		it("starts on the first page and keeps the scroll position", async () => {
			const scrollTo = vi.spyOn(window, "scrollTo");
			const wrapper = mount();

			await wrapper.vm.reload({ searchterm: "wp" });

			expect(wrapper.$panel.reload).toHaveBeenCalledWith({
				query: { page: null, searchterm: "wp" },
			});
			expect(scrollTo).toHaveBeenCalledWith(0, window.scrollY);
		});
	});

	describe("filter", () => {
		it("is closed without a searchterm", () => {
			expect(mount().find("k-input").exists()).toBe(false);
		});

		it("is open with a searchterm", () => {
			const wrapper = mount({ searchterm: "wp" });
			expect(wrapper.find("k-input").attributes("value")).toBe("wp");
		});

		it("reloads with the searchterm", () => {
			const wrapper = mount({ searchterm: "w" });

			wrapper.findComponent(Input).vm.$emit("input", "wp");
			expect(wrapper.$panel.reload).toHaveBeenLastCalledWith({
				query: { page: null, searchterm: "wp" },
			});
		});

		it("reloads without an empty searchterm", () => {
			const wrapper = mount({ searchterm: "w" });

			wrapper.findComponent(Input).vm.$emit("input", "");
			expect(wrapper.$panel.reload).toHaveBeenLastCalledWith({
				query: { page: null, searchterm: null },
			});
		});

		it("clears the searchterm when closing", async () => {
			const wrapper = mount({ searchterm: "wp" });
			await wrapper.vm.toggleSearch();

			expect(wrapper.vm.searching).toBe(false);
			expect(wrapper.$panel.reload).toHaveBeenCalledWith({
				query: { page: null, searchterm: null },
			});
		});

		it("clears a term with escape before it closes", async () => {
			const wrapper = mount({ searchterm: "wp" });
			const input = wrapper.find("k-input");

			await input.trigger("keydown", { key: "Escape" });
			expect(wrapper.vm.searching).toBe(true);
			expect(wrapper.vm.term).toBe(null);

			await input.trigger("keydown", { key: "Escape" });
			expect(wrapper.vm.searching).toBe(false);
		});
	});
});
