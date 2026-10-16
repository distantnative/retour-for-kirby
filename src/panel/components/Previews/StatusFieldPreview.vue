<template>
	<div
		:title="`${column.label}: ${value}`"
		class="k-text k-status-field-preview"
	>
		<k-icon :color="color" type="circle-filled" />
		<code>{{ value }}</code>
	</div>
</template>

<script>
import { color } from "../../helpers/status";

export default {
	inheritAttrs: false,
	props: {
		value: Number,
		column: Object,
	},
	computed: {
		color() {
			return color(this.value);
		},
	},
};
</script>

<style>
.k-status-field-preview {
	display: flex;
	gap: var(--spacing-2);
	padding-inline: var(--table-cell-padding);
}
</style>
