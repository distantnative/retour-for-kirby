import { describe, expect, it } from "@test/panel";
import { mount as vueMount } from "@vue/test-utils";
import CountFieldPreview from "./CountFieldPreview.vue";

function mount(
	attrs = {},
	value = 1234,
	last: string | null = "2026-10-17 14:05:09",
) {
	return vueMount(CountFieldPreview, {
		props: { row: { last }, value },
		attrs,
	});
}

describe("CountFieldPreview.vue", () => {
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-count-field-preview");
		it.inheritsNoAttrs(mount);
	});

	describe("value prop", () => {
		it("shows the hits formatted in the Panel's language", () => {
			const wrapper = mount();
			expect(wrapper.text()).toBe("1,234");
			expect(wrapper.find("k-icon").attributes("type")).toBe("history");
		});

		it("shows a dash without hits", () => {
			const wrapper = mount({}, 0, null);
			expect(wrapper.find("k-icon").exists()).toBe(false);
			expect(wrapper.find("p").text()).toBe("–");
		});
	});

	describe("title", () => {
		it("shows the date of the last hit", () => {
			expect(mount().attributes("title")).toBe(
				"retour.hits.last: 17 Oct 2026, 14:05:09",
			);
		});

		it("shows a dash without a last hit", () => {
			expect(mount({}, 0, null).attributes("title")).toBe(
				"retour.hits.last: –",
			);
		});
	});
});
