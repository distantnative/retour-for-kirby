<template>
	<div :title="title" class="k-count-field-preview">
		<template v-if="value > 0">
			{{ hits }}
			<k-icon type="history" />
		</template>
		<p v-else>–</p>
	</div>
</template>

<script>
export default {
	inheritAttrs: false,
	props: {
		row: Object,
		value: Number,
	},
	computed: {
		hits() {
			return new Intl.NumberFormat(this.$library.dayjs.locale()).format(
				this.value,
			);
		},
		title() {
			const last = this.row.last
				? this.$library.dayjs(this.row.last).format("D MMM YYYY, HH:mm:ss")
				: "–";

			return `${this.$t("retour.hits.last")}: ${last}`;
		},
	},
};
</script>

<style>
.k-count-field-preview {
	--icon-color: var(--color-text-dimmed);
	--icon-size: 14px;

	display: flex;
	flex-direction: row-reverse;
	align-items: center;
	justify-content: space-between;
	gap: var(--spacing-2);
	padding-inline: var(--table-cell-padding);
}
.k-count-field-preview p {
	color: var(--color-text-dimmed);
}
</style>
