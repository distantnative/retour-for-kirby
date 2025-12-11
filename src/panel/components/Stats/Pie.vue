<template>
	<figure class="chart-pie">
		<div class="graph" :style="'--gradient: ' + gradient" />

		<figcaption>
			<template v-for="(segment, index) in data" :key="segment.label">
				<k-icon :style="'--color:' + segment.color" :type="icons[index]" />
				<span>
					{{ new Intl.NumberFormat().format(segment.data) }}
				</span>
				<span>
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
	computed: {
		gradient() {
			let gradient = "";
			let size = 0;
			const deg = this.total / 180;

			for (let i = 0; i < this.data.length; i++) {
				gradient += `${this.data[i].color} ${size}deg,`;
				size += this.data[i].data / deg;
				gradient += `${this.data[i].color} ${size}deg,`;
			}

			gradient += "transparent 180deg";
			return gradient;
		},
		icons() {
			return ["shuffle", "check-double", "cloud-off"];
		},
		total() {
			return this.data.reduce((i, x) => (i += x.data), 0);
		},
	},
};
</script>

<style>
.chart-pie {
	display: flex;
	flex-direction: column;
	align-items: center;
	padding-block: var(--spacing-6);
}
.chart-pie > .graph {
	width: 100%;
	padding-bottom: 100%;
	opacity: 0.85;
	background: var(--color-gray-800);
	background-image: conic-gradient(from -90deg, var(--gradient));
	border-radius: 50%;
	clip-path: polygon(0% 0%, 0% 50%, 100% 50%, 100% 0%);
	margin-bottom: calc(-50% + var(--spacing-6));
}
.chart-pie figcaption {
	display: grid;
	grid-template-columns: auto auto auto;
	column-gap: 0.5ch;
	row-gap: var(--spacing-3);
}
.chart-pie figcaption :nth-child(3n + 2) {
	padding-inline-start: var(--spacing-2);
	text-align: right;
}
.chart-pie figcaption .k-icon {
	color: var(--color);
}
</style>
