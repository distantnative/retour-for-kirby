import { withBase } from "vitepress";
import DefaultTheme from "vitepress/theme";
import { h } from "vue";
import "./custom.css";

export default {
	extends: DefaultTheme,
	Layout: () =>
		h(DefaultTheme.Layout, null, {
			"home-features-before": () =>
				h("div", { class: "home-screenshot" }, [
					h("img", {
						src: withBase("/retour.webp"),
						alt: "Retour view in the Panel"
					})
				])
		})
};
