<template>
	<p class="k-text k-url-field-preview k-path-field-preview">
		<k-link
			:to="link"
			:title="`${column.label}: ${value}`"
			target="_blank"
			@click.stop
		>
			<span>{{ value }}</span>
		</k-link>
	</p>
</template>

<script>
export default {
	inheritAttrs: false,
	props: {
		column: {
			type: Object,
			default: () => ({}),
		},
		value: String,
	},
	computed: {
		link() {
			// URLs of any scheme as they are, `makeAbsolute()` only knows http(s)
			if (this.value.includes("://")) {
				return this.value;
			}

			// an absolute site URL keeps every path on the site
			const site = new URL(this.$panel.urls.site, window.location.origin);
			return this.$helper.url.makeAbsolute(this.value, site);
		},
	},
};
</script>
