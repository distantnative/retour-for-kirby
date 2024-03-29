import { defineConfig } from "vitepress";

// https://vitepress.dev/reference/site-config
export default defineConfig({
	title: "Retour",
	description: "Redirects and 404 tracking for Kirby",
	appearance: "force-dark",
	themeConfig: {
		logo: "/scribbles.svg",

		nav: [
			{ text: "Home", link: "/" },
			{ text: "Docs", link: "/quickstart" },
			{
				text: "Changelog",
				link: "https://github.com/distantnative/retour-for-kirby/releases",
			},
			{
				text: "Donate",
				link: "https://paypal.me/distantnative",
			},
		],

		sidebar: [
			{
				text: "Docs",
				items: [
					{ text: "Getting started", link: "/quickstart" },
					{ text: "Redirects", link: "/redirects" },
					{ text: "404 tracking", link: "/failures" },
				],
			},
			{
				text: "Config",
				items: [
					{ text: "Options", link: "/options" },
					{ text: "Translations", link: "/i18n" },
				],
			},
			{
				text: "Help",
				items: [
					{ text: "Troubleshooting", link: "/troubleshooting" },
					{
						text: "Support development",
						link: "https://paypal.me/distantnative",
					},
				],
			},
		],

		socialLinks: [
			{
				icon: "github",
				link: "https://github.com/distantnative/retour-for-kirby",
			},
			{
				icon: "mastodon",
				link: "https://chaos.social/@distantnative",
			},
		],
	},
});
