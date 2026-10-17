import { describe, expect, it } from "@test/panel";
import { mount as vueMount } from "@vue/test-utils";
import StatusFieldPreview from "./StatusFieldPreview.vue";

function mount(attrs = {}, value: number | string = 301) {
	return vueMount(StatusFieldPreview, {
		props: { column: { label: "Status" }, value },
		attrs,
	});
}

describe("StatusFieldPreview.vue", () => {
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-status-field-preview");
		it.inheritsNoAttrs(mount);

		it("names the column and status in the title", () => {
			expect(mount().attributes("title")).toBe("Status: 301");
		});
	});

	describe("value prop", () => {
		it("shows the status code next to its color", () => {
			const wrapper = mount({}, 404);
			expect(wrapper.text()).toBe("404");
			expect(wrapper.find("k-icon").attributes("color")).toBe(
				"var(--color-red-500)",
			);
		});

		it("shows a dimmed dash and a gray dot for disabled redirects", () => {
			const wrapper = mount({}, "disabled");
			expect(wrapper.find("p").text()).toBe("–");
			expect(wrapper.find("k-icon").attributes("color")).toBe(
				"var(--color-gray-400)",
			);
			expect(wrapper.attributes("title")).toBe("Status: disabled");
		});
	});
});
