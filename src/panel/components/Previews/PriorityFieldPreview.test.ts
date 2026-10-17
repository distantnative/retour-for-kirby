import { describe, expect, it } from "@test/panel";
import { mount as vueMount } from "@vue/test-utils";
import PriorityFieldPreview from "./PriorityFieldPreview.vue";

function mount(attrs = {}, value = true) {
	return vueMount(PriorityFieldPreview, {
		props: { column: { label: "Prio" }, value },
		attrs,
	});
}

describe("PriorityFieldPreview.vue", () => {
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-priority-field-preview");
		it.inheritsNoAttrs(mount);
	});

	describe("value prop", () => {
		it("shows a labeled bolt for priority", () => {
			const wrapper = mount();
			expect(wrapper.attributes("title")).toBe("Prio");
			expect(wrapper.find("k-icon").attributes()).toMatchObject({
				alt: "Prio",
				type: "bolt",
			});
		});

		it("shows a dash without priority", () => {
			const wrapper = mount({}, false);
			expect(wrapper.attributes("title")).toBeUndefined();
			expect(wrapper.find("k-icon").exists()).toBe(false);
			expect(wrapper.find("p").text()).toBe("–");
		});
	});
});
