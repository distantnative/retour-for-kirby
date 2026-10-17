<template>
	<div
		:title="`${column.label}: ${value}`"
		class="k-text k-status-field-preview"
	>
		<k-icon :color="color" type="circle-filled" />
		<p v-if="value === 'disabled'">–</p>
		<template v-else>{{ value }}</template>
	</div>
</template>

<script>
import { color } from "../../helpers/status";

export default {
	inheritAttrs: false,
	props: {
		value: [Number, String],
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
	align-items: center;
	gap: var(--spacing-2);
	padding-inline: var(--table-cell-padding);
	font-family: var(--font-mono);
}
.k-status-field-preview p {
	color: var(--color-text-dimmed);
}
</style>
