<template>
	<figure class="k-retour-pie">
		<div class="k-retour-pie-graph" :style="styles" />

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
import { pie } from "../../helpers/chart";

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

.k-retour-pie {
	display: flex;
	flex-direction: column;
	align-items: center;
	container-type: inline-size;
}

.k-retour-pie-graph {
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
