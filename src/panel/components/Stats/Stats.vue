<template>
	<section class="k-retour-stats">
		<pie :data="pie" />
		<timeline :data="areas" :timespan="timespan" />
	</section>
</template>

<script>
import Pie from "./Pie.vue";
import Timeline from "./Timeline.vue";

export default {
	components: {
		Pie,
		Timeline,
	},
	props: {
		data: Array,
		timespan: Object,
	},
	computed: {
		areas() {
			return this.data.map((entry) => ({
				label: entry.date,
				areas: [
					{
						data: entry.redirected,
						color: "var(--color-blue-600)",
					},
					{
						data: entry.resolved,
						color: "var(--color-gray-300)",
					},
					{
						data: entry.failed,
						color: "var(--color-red-600)",
					},
				],
			}));
		},
		pie() {
			return [
				{
					data: this.data.reduce((i, x) => (i += x.redirected), 0),
					color: "var(--color-blue-600)",
					label: this.$t("retour.stats.redirected"),
				},
				{
					data: this.data.reduce((i, x) => (i += x.resolved), 0),
					color: "var(--color-gray-300)",
					label: this.$t("retour.stats.resolved"),
				},
				{
					data: this.data.reduce((i, x) => (i += x.failed), 0),
					color: "var(--color-red-600)",
					label: this.$t("retour.stats.failed"),
				},
			];
		},
	},
};
</script>

<style>
.k-retour-stats {
	padding: var(--spacing-6);
	color: var(--color-white);
	background: light-dark(var(--color-gray-900), var(--color-gray-950));
	border-radius: var(--rounded-lg);
	margin-bottom: var(--spacing-6);
}

@container (max-width: 42rem) {
	.k-retour-stats {
		display: flex;
		flex-direction: column;
		gap: 2rem;
	}
}

@container (min-width: 42rem) {
	.k-retour-stats {
		display: grid;
		grid-template-columns: 1fr 3fr;
		gap: 2rem;
	}
}
</style>
