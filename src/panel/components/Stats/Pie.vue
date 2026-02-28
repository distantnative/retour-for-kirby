<template>
	<figure class="chart-pie">
		<div class="graph" :style="styles" />

		<figcaption>
			<template v-for="(segment, index) in data">
				<k-icon
					:key="'icon-' + segment.label"
					:style="'--color:' + segment.color"
					:type="$options.icons[index]"
				/>
				<span :key="'count-' + segment.label">
					{{ $options.formatter.format(segment.data) }}
				</span>
				<span :key="'label-' + segment.label">
					{{ segment.label }}
				</span>
			</template>
		</figcaption>
	</figure>
</template>

<script>
export default {
	props: {
		data: Array,
	},
	icons: ["shuffle", "check-double", "cloud-off"],
	formatter: new Intl.NumberFormat(),
	computed: {
		total() {
			return this.data.reduce((i, x) => (i += x.data), 0);
		},
		styles() {
			if (this.total === 0) {
				return {
					"--pie-c0": "transparent",
					"--pie-c1": "transparent",
					"--pie-c2": "transparent",
					"--pie-a0": "0deg",
					"--pie-a1": "0deg",
				};
			}

			const deg = this.total / 180;
			const a0 = this.data[0].data / deg;
			const a1 = a0 + this.data[1].data / deg;

			return {
				"--pie-c0": this.data[0].color,
				"--pie-c1": this.data[1].color,
				"--pie-c2": this.data[2].color,
				"--pie-a0": `${a0}deg`,
				"--pie-a1": `${a1}deg`,
			};
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
