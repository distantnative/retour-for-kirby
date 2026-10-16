<template>
	<figure class="chart-pie">
		<div class="graph" :style="styles" />

		<figcaption>
			<template v-for="(segment, index) in data" :key="segment.label">
				<k-icon
					:style="'--color:' + segment.color"
					:type="$options.icons[index]"
				/>
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
import { pie } from "../../helpers/chart";

export default {
	props: {
		data: Array,
	},
	icons: ["redirect", "url", "url-broken"],
	computed: {
		formatter() {
			return new Intl.NumberFormat(this.$library.dayjs.locale());
		},
		styles() {
			return pie(this.data);
		},
	},
};
</script>

<style>
@property --pie-a0 {
	syntax: "<angle>";
	inherits: false;
	initial-value: 0deg;
}
@property --pie-a1 {
	syntax: "<angle>";
	inherits: false;
	initial-value: 0deg;
}

.chart-pie {
	display: flex;
	flex-direction: column;
	align-items: center;
	container-type: inline-size;
}

.chart-pie > .graph {
	width: 100%;
	aspect-ratio: 1/1;
	opacity: 0.85;
	background: var(--color-gray-800);
	background-image: conic-gradient(
		from -90deg,
		var(--pie-c0) 0deg var(--pie-a0),
		var(--pie-c1) var(--pie-a0) var(--pie-a1),
		var(--pie-c2) var(--pie-a1) 180deg,
		transparent 180deg
	);
	border-radius: 50%;
	clip-path: polygon(0% 0%, 0% 50%, 100% 50%, 100% 0%);
	margin-bottom: calc(-50% + var(--spacing-6));
	transition:
		--pie-a0 0.3s ease-out,
		--pie-a1 0.3s ease-out;
}

.chart-pie figcaption {
	display: grid;
	grid-template-columns: auto auto auto;
	column-gap: 0.4ch;
	row-gap: var(--spacing-3);
}
.chart-pie figcaption :nth-child(3n + 2) {
	padding-inline-start: var(--spacing-2);
	text-align: right;
}
.chart-pie figcaption .k-icon {
	color: var(--color);
}

@container (min-width: 18rem) {
	.chart-pie figcaption {
		grid-template-columns: repeat(9, auto);
	}
	.chart-pie figcaption .k-icon:not(:first-child) {
		margin-inline-start: var(--spacing-4);
	}
}
</style>
