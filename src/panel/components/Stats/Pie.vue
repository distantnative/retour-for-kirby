<template>
	<figure class="k-retour-pie">
		<div class="k-retour-pie-chart">
			<div class="k-retour-pie-graph" :style="styles" />
			<p>
				<strong>{{ formatter.format(total) }}</strong>
				{{ $t("retour.stats.requests") }}
			</p>
		</div>

		<figcaption>
			<p v-for="segment in data" :key="segment.label">
				<k-icon :color="segment.color" :type="segment.icon" />
				<span>{{ formatter.format(segment.data) }}</span>
				<span>{{ segment.label }}</span>
			</p>
		</figcaption>
	</figure>
</template>

<script>
import { pie, sum } from "../../helpers/chart";

export default {
	props: {
		data: Array,
	},
	computed: {
		formatter() {
			return new Intl.NumberFormat(this.$library.dayjs.locale());
		},
		styles() {
			return pie(this.data);
		},
		total() {
			return sum(this.data);
		},
	},
};
</script>

<style>
@property --pie-a0 {
	syntax: "<angle>";
	inherits: true;
	initial-value: 0deg;
}
@property --pie-a1 {
	syntax: "<angle>";
	inherits: true;
	initial-value: 0deg;
}

.k-retour-pie {
	display: flex;
	flex-direction: column;
	align-items: center;
	container-type: inline-size;
}

/* the half ring with the total inside */
.k-retour-pie-chart {
	position: relative;
	align-self: stretch;
}
/* a half ring, darker towards the inside
   like the timeline towards the bottom */
.k-retour-pie-graph {
	position: relative;
	width: 100%;
	aspect-ratio: 1/1;
	background-image:
		radial-gradient(closest-side, rgb(0 0 0 / 0.3) 62%, transparent),
		conic-gradient(
			from -90deg,
			var(--pie-c0) 0deg var(--pie-a0),
			var(--pie-c1) 0 var(--pie-a1),
			var(--pie-c2) 0 180deg,
			transparent 180deg
		);
	border-radius: 50%;
	clip-path: polygon(0% 0%, 0% 50%, 100% 50%, 100% 0%);
	mask: radial-gradient(closest-side, transparent 62%, #000 calc(62% + 0.5px));
	margin-bottom: calc(-50% + var(--spacing-6));
	transition:
		--pie-a0 0.3s ease-out,
		--pie-a1 0.3s ease-out;
}

/* the gaps between the slices: lines in the background color,
   which (unlike gaps in the gradient) have smooth edges */
.k-retour-pie-graph::before,
.k-retour-pie-graph::after {
	content: "";
	position: absolute;
	bottom: 50%;
	left: calc(50% - 1px);
	width: 2px;
	height: 50%;
	background: var(--retour-stats-color-back);
	transform-origin: bottom;
	rotate: calc(var(--pie-a0) - 90deg);
}
.k-retour-pie-graph::after {
	rotate: calc(var(--pie-a1) - 90deg);
}

/* the total inside the half ring */
.k-retour-pie-chart > p {
	position: absolute;
	inset-inline: 0;
	top: 0;
	aspect-ratio: 2/1;
	display: flex;
	flex-direction: column;
	justify-content: end;
	align-items: center;
	padding-bottom: var(--spacing-1);
	font-size: var(--text-xs);
	line-height: 1.1;
	color: var(--color-gray-500);
}
.k-retour-pie-chart > p strong {
	font-size: var(--text-3xl);
	font-weight: var(--font-bold);
	color: var(--color-white);
}

/* stacked: icons, numbers and labels in aligned columns */
.k-retour-pie figcaption {
	display: grid;
	grid-template-columns: auto auto auto;
	column-gap: 0.4ch;
	row-gap: var(--spacing-3);
}
.k-retour-pie figcaption p {
	display: grid;
	grid-column: 1 / -1;
	grid-template-columns: subgrid;
	align-items: center;
}
.k-retour-pie figcaption span:first-of-type {
	padding-inline-start: var(--spacing-2);
	text-align: end;
}

/* in a row that wraps between the entries */
@container (min-width: 18rem) {
	.k-retour-pie figcaption {
		display: flex;
		flex-wrap: wrap;
		justify-content: center;
		gap: var(--spacing-3) var(--spacing-4);
	}
	.k-retour-pie figcaption p {
		display: flex;
		gap: 0.4ch;
	}
}
</style>
