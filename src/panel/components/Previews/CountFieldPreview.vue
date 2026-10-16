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
		last() {
			if (this.row.last) {
				return this.$library
					.dayjs(this.row.last)
					.format("D MMM YYYY, HH:mm:ss");
			}

			return "–";
		},
		title() {
			return this.$t("retour.hits.last") + ": " + this.last;
		},
	},
};
</script>

<style>
.k-count-field-preview {
	display: flex;
	flex-direction: row-reverse;
	justify-content: space-between;
	padding-inline: var(--spacing-2);
}
.k-count-field-preview p {
	color: var(--color-gray-400);
}
.k-count-field-preview .k-icon {
	--icon-color: var(--color-gray-400);
	transform: scale(0.8);
}
</style>
