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

	color: var(--color-white);
	margin-bottom: var(--spacing-6);
}

/* stacked in one box, the totals without the ring */
@container main (max-width: 40rem) {
	.k-retour-stats {
		display: flex;
		flex-direction: column;
		padding: var(--spacing-6) var(--spacing-3) var(--spacing-6) var(--spacing-6);
		background: var(--retour-stats-color-back);
		border-radius: var(--rounded-lg);
	}
	.k-retour-stats .k-retour-pie-chart {
		display: none;
	}
}

/* side by side, each in its own box */
@container main (min-width: 40rem) {
	.k-retour-stats {
		display: grid;
		grid-template-columns: calc(16rem + 2 * var(--spacing-6)) 1fr;
		gap: var(--spacing-3);
	}
	.k-retour-stats > * {
		background: var(--retour-stats-color-back);
		border-radius: var(--rounded-lg);
	}
	.k-retour-stats > .k-retour-pie {
		justify-content: center;
		padding: var(--spacing-6);
	}
	/* evens out the space below the ring and below the legend */
	.k-retour-stats .k-retour-pie figcaption {
		translate: 0 var(--spacing-2);
	}
	.k-retour-stats > .k-retour-timeline {
		padding-inline: var(--spacing-4) var(--spacing-3);
		padding-bottom: var(--spacing-4);
	}
}
</style>
