<template>
	<div
		:title="`${column.label}: ${value}`"
		class="k-text k-status-field-preview"
	>
		<k-icon type="circle-filled" :style="'color: ' + color" />
		<code>{{ value }}</code>
	</div>
</template>

<script>
import color from "../../mixins/color.js";

export default {
	mixins: [color],
	props: {
		value: Number,
		row: Object,
		column: Object,
	},
};
</script>

<style>
.k-status-field-preview {
	--code-inline-color-back: var(--color-blue-200);
	--code-inline-color-border: var(--color-blue-400);

	display: flex;
	gap: var(--spacing-2);
	padding-inline: var(--spacing-2);
}
</style>
