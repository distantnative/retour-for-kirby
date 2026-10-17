<template>
	<figure class="k-retour-pie">
		<div class="k-retour-pie-graph" :style="styles" />
		<p>
			<strong>{{ formatter.format(total) }}</strong>
			{{ $t("retour.stats.requests") }}
		</p>

		<figcaption>
			<template v-for="segment in data" :key="segment.label">
				<k-icon :color="segment.color" :type="segment.icon" />
				<span>
					{{ formatter.format(segment.data) }}
				</span>
				<span>
					{{ segment.label }}
				</span>
			</template>
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
	position: relative;
	display: flex;
	flex-direction: column;
	align-items: center;
	container-type: inline-size;
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
.k-retour-pie > p {
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
.k-retour-pie > p strong {
	font-size: var(--text-3xl);
	font-weight: var(--font-bold);
	color: var(--color-white);
}

.k-retour-pie figcaption {
	display: grid;
	grid-template-columns: auto auto auto;
	column-gap: 0.4ch;
	row-gap: var(--spacing-3);
}
.k-retour-pie figcaption :nth-child(3n + 2) {
	padding-inline-start: var(--spacing-2);
	text-align: end;
}

@container (min-width: 18rem) {
	.k-retour-pie figcaption {
		grid-template-columns: repeat(9, auto);
	}
	.k-retour-pie figcaption .k-icon:not(:first-child) {
		margin-inline-start: var(--spacing-4);
	}
}
</style>
