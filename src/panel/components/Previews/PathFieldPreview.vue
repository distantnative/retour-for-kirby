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
	props: {
		column: {
			type: Object,
			default: () => ({}),
		},
		field: Object,
		value: [String, Object],
	},
	computed: {
		link() {
			// an absolute site URL keeps every path on the site
			const site = new URL(this.$panel.urls.site, window.location.origin);
			return this.$helper.url.makeAbsolute(this.value, site);
		},
	},
};
</script>
