<template>
	<section class="k-retour-stats">
		<pie :data="pie" />
		<timeline :data="areas" :timespan="timespan" />
	</section>
</template>

<script>
import Pie from "./Pie.vue";
import Timeline from "./Timeline.vue";

const series = [
	{
		key: "redirected",
		color: "var(--color-blue-650)",
		label: "retour.stats.redirected",
	},
	{
		key: "resolved",
		color: "var(--color-gray-250)",
		label: "retour.stats.resolved",
	},
	{
		key: "failed",
		color: "var(--color-red-650)",
		label: "retour.stats.failed",
	},
];

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
				areas: series.map(({ key, color }) => ({
					data: entry[key],
					color,
				})),
			}));
		},
		pie() {
			return series.map(({ key, color, label }) => ({
				data: this.data.reduce((i, x) => (i += x[key]), 0),
				color,
				label: this.$t(label),
			}));
		},
	},
};
</script>

<style>
.k-retour-stats {
	padding: var(--spacing-3) var(--spacing-3) var(--spacing-8) var(--spacing-6);
	color: var(--color-white);
	background: light-dark(
		var(--color-gray-900),
		var(--color-gray-950, var(--color-gray-900))
	);
	border-radius: var(--rounded-lg);
	margin-bottom: var(--spacing-6);
}

@container (max-width: 40rem) {
	.k-retour-stats {
		display: flex;
		flex-direction: column;
		padding-block: var(--spacing-6);
	}
}

@container (min-width: 40rem) {
	.k-retour-stats {
		display: grid;
		align-items: center;
		grid-template-columns: 16rem auto;
	}
}
</style>
