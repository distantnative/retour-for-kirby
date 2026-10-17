export * from "vitest";
import { it as vitestIt, expect } from "vitest";
import type { VueWrapper } from "@vue/test-utils";

type Mount = (attrs?: Record<string, unknown>) => VueWrapper;

export const it = Object.assign(vitestIt, {
	inheritsNoAttrs(mount: Mount) {
		vitestIt("does not inherit random attrs", () => {
			const wrapper = mount({ "data-foo": "bar" });
			expect(wrapper.attributes("data-foo")).toBeUndefined();
		});
	},
	rendersAs(mount: Mount, tag: string, className?: string) {
		vitestIt(
			`renders a <${tag}>${className ? ` with class ${className}` : ""}`,
			() => {
				const wrapper = mount();
				expect(wrapper.element.tagName).toBe(tag);

				if (className) {
					expect(wrapper.classes()).toContain(className);
				}
			},
		);
	},
});
