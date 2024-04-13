import { defineConfig } from "vitepress";

// https://vitepress.dev/reference/site-config
export default defineConfig({
	title: "Retour",
	description: "Redirects and 404 tracking for Kirby",
	lang: "en-US",
	base: "/retour-for-kirby/",
	appearance: false,
	cleanUrls: true,
	head: [["link", { rel: "og:image", href: "/retour-for-kirby/ogimage.png" }]],
	themeConfig: {
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

		search: {
			provider: "local",
		},

		sidebar: [
			{
				text: "Documentation",
				items: [
					{ text: "Getting started", link: "/quickstart" },
					{ text: "Redirects", link: "/redirects" },
					{ text: "404 tracking", link: "/failures" },
				],
			},
			{
				text: "Configuration",
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
						text: "Report a bug",
						link: "https://github.com/distantnative/retour-for-kirby/issues",
					},
					{ text: "Report a vulnerability", link: "/security" },
				],
			},
			{
				text: "Support development",
				items: [
					{ text: "How to contribute", link: "/contribute" },
					{
						text: "Donate",
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

		footer: {
			message:
				"Retour is made for <a href='https://getkirby.com'>Kirby CMS</a> with love 💛",
		},
	},
});
