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
		icon: "redirect",
		label: "retour.stats.redirected",
	},
	{
		key: "resolved",
		color: "var(--color-gray-250)",
		icon: "url",
		label: "retour.stats.resolved",
	},
	{
		key: "failed",
		color: "var(--color-red-650)",
		icon: "url-broken",
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
			return series.map(({ key, color, icon, label }) => ({
				data: this.data.reduce((total, entry) => total + entry[key], 0),
				color,
				icon,
				label: this.$t(label),
			}));
		},
	},
};
</script>

<style>
.k-retour-stats {
	--retour-stats-color-back: light-dark(
		var(--color-gray-900),
		var(--color-gray-950)
	);

	padding-block: var(--spacing-3) var(--spacing-8);
	padding-inline: var(--spacing-6) var(--spacing-3);
	color: var(--color-white);
	background: var(--retour-stats-color-back);
	border-radius: var(--rounded-lg);
	margin-bottom: var(--spacing-6);
}

@container main (max-width: 40rem) {
	.k-retour-stats {
		display: flex;
		flex-direction: column;
		padding-block: var(--spacing-6);
	}
	.k-retour-stats :is(.k-retour-pie-graph, .k-retour-pie > p) {
		display: none;
	}
}

@container main (min-width: 40rem) {
	.k-retour-stats {
		display: grid;
		align-items: center;
		grid-template-columns: 16rem auto;
		column-gap: var(--spacing-12);
	}
}
</style>
