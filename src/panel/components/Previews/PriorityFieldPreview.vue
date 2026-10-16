<template>
	<div :title="value ? column.label : null" class="k-priority-field-preview">
		<k-icon v-if="value" :alt="column.label" type="bolt" />
		<p v-else>–</p>
	</div>
</template>

<script>
export default {
	inheritAttrs: false,
	props: {
		value: Boolean,
		column: Object,
	},
};
</script>

<style>
.k-priority-field-preview {
	--icon-size: 14px;

	display: flex;
	justify-content: center;
}
.k-priority-field-preview p {
	color: var(--color-text-dimmed);
}
</style>
