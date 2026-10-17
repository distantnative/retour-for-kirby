import { describe, expect, it, vi } from "@test/panel";
import { mount as vueMount } from "@vue/test-utils";
import PathFieldPreview from "./PathFieldPreview.vue";

function mount(attrs = {}, value = "blog/old") {
	const makeAbsolute = vi.fn((path: string, origin: URL) => `${origin}${path}`);

	const wrapper = vueMount(PathFieldPreview, {
		props: { column: { label: "Path" }, value },
		attrs,
		global: {
			mocks: {
				$helper: { url: { makeAbsolute } },
				$panel: { urls: { site: "https://example.com/" } },
			},
		},
	});

	return Object.assign(wrapper, { makeAbsolute });
}

describe("PathFieldPreview.vue", () => {
	describe("element", () => {
		it.rendersAs(mount, "P", "k-path-field-preview");
		it.inheritsNoAttrs(mount);

		it("opens the link in a new tab", () => {
			expect(mount().find("k-link").attributes("target")).toBe("_blank");
		});

		it("names the column and path in the title", () => {
			expect(mount().find("k-link").attributes("title")).toBe("Path: blog/old");
		});
	});

	describe("link", () => {
		it("links paths on the site", () => {
			const wrapper = mount();
			expect(wrapper.makeAbsolute).toHaveBeenCalledWith(
				"blog/old",
				new URL("https://example.com/"),
			);
			expect(wrapper.find("k-link").attributes("to")).toBe(
				"https://example.com/blog/old",
			);
		});

		it("links paths that look like a scheme on the site", () => {
			const wrapper = mount({}, "mailto:info@example.com");
			expect(wrapper.find("k-link").attributes("to")).toBe(
				"https://example.com/mailto:info@example.com",
			);
		});

		it.each([
			"https://www.google.com/",
			"android-app://com.google.android.gm/",
		])("links the URL %s as it is", (url) => {
			const wrapper = mount({}, url);
			expect(wrapper.makeAbsolute).not.toHaveBeenCalled();
			expect(wrapper.find("k-link").attributes("to")).toBe(url);
		});
	});
});
