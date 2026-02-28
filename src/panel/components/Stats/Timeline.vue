<template>
	<div :data-subunit="subunit" class="chart-areas">
		<table>
			<thead>
				<tr>
					<th v-for="tick in axisY" :key="tick">
						{{ tick }}
					</th>
				</tr>
			</thead>
			<tbody>
				<tr
					v-for="(segment, segmentIndex) in data"
					:key="segmentIndex"
					@click="zoom(segment)"
				>
					<td
						v-for="(area, areaIndex) in segment.areas"
						:key="segmentIndex + '_' + areaIndex"
						:style="`--color: ${color(segmentIndex, area)}; ${clip(
							segmentIndex,
							areaIndex,
						)}`"
					/>
				</tr>
			</tbody>
			<tfoot :data-less="data.length > 31">
				<tr
					v-for="segment in data"
					:key="segment.label"
					:data-current="isCurrent(segment)"
					@click="zoom(segment)"
				>
					<td>{{ label(segment) }}</td>
				</tr>
			</tfoot>
		</table>
	</div>
</template>

<script>
export default {
	props: {
		data: Array,
		timespan: Object,
	},
	computed: {
		axisY() {
			const span = this.max / 5;
			return [1, 2, 3, 4, 5].map((x) => {
				let tick = x * span;

				if (tick >= 1000000) {
					tick = Math.floor(tick / 100000) / 10 + "M";
				} else if (tick >= 1000) {
					tick = Math.floor(tick / 100) / 10 + "k";
				}

				return tick;
			});
		},
		bounds() {
			return this.data.map((segment) =>
				segment.areas.map((_, area) => {
					const stack = segment.areas
						.slice(0, area)
						.reduce((i, x) => (i += x.data), 0);
					return {
						path: stack + segment.areas[area].data,
						mask: stack,
					};
				}),
			);
		},
		format() {
			return (
				{
					day: "HH",
					week: "ddd",
					month: "D",
					year: "MMM",
					months: "MMM YY",
				}[this.timespan.unit] ?? "D MMM"
			);
		},
		max() {
			let max = Math.max(
				...this.data.map((segment) => {
					return segment.areas.reduce((i, x) => (i += x.data), 0);
				}),
			);

			if (max > 0) {
				const digits = max.toString().length;
				const next = Math.pow(10, digits) / 4;
				return Math.ceil((max * 1.1) / next) * next;
			}

			return 5;
		},
		subunit() {
			return (
				{
					day: "hour",
					year: "month",
				}[this.timespan.unit] ?? "day"
			);
		},
		today() {
			return this.$library.dayjs();
		},
	},
	methods: {
		clip(segment, area) {
			const current = this.bounds[segment][area];
			const next = this.bounds[segment + 1]?.[area] ?? {
				path: 0,
				mask: 0,
			};

			return `
				--p0: ${current.path / this.max};
				--p1: ${next.path / this.max};
				--m0: ${current.mask / this.max};
				--m1: ${next.mask / this.max};
			`;
		},
		color(segment, area) {
			const next = this.data[segment + 1];

			if (
				next &&
				this.$library.dayjs(next.label).isAfter(this.today, this.subunit)
			) {
				return "transparent";
			}

			return area.color;
		},
		isCurrent(segment) {
			return this.$library
				.dayjs(segment.label)
				.isSame(this.today, this.subunit);
		},
		label(segment) {
			return this.$library.dayjs(segment.label).format(this.format);
		},
		zoom(segment) {
			if (this.subunit === "hour") {
				return;
			}

			const date = this.$library.dayjs(segment.label);

			this.$reload({
				query: {
					from: date.startOf(this.subunit).format("YYYY-MM-DD"),
					to: date.endOf(this.subunit).format("YYYY-MM-DD"),
				},
			});
		},
	},
};
</script>

<style>
.chart-areas {
	overflow-x: auto;
	overflow-y: hidden;
	padding-block-start: var(--spacing-8);
	padding-inline: var(--spacing-3);
}

.chart-areas table {
	--height: 250px;

	position: relative;
	width: calc(100% - 3rem);
	height: var(--height);
	margin-inline-start: 2rem;
	margin-inline-end: 1rem;
	border-collapse: collapse;
	border-spacing: 0;
}
.chart-areas thead {
	position: absolute;
	top: 0;
	right: calc(100%);
	height: var(--height);
}
.chart-areas thead tr {
	display: flex;
	height: var(--height);
	flex-direction: column-reverse;
	padding-right: var(--spacing-3);
}
.chart-areas th {
	flex: 1;
	transform: translateY(calc(-1 * var(--spacing-3) / 2));
	font-size: var(--text-xs);
	font-weight: normal;
	text-align: right;
	color: var(--color-text-dimmed);
}
.chart-areas tbody {
	--grid-x: linear-gradient(
		to right,
		var(--color-gray-800) 33%,
		transparent 0%
	);

	height: var(--height);
	background-image:
		var(--grid-x), var(--grid-x), var(--grid-x), var(--grid-x), var(--grid-x),
		var(--grid-x);
	background-size:
		1px 1px,
		3px 1px,
		3px 1px,
		3px 1px,
		3px 1px,
		1px 1px;
	background-repeat: repeat-x;
	background-position:
		center 0%,
		center 20%,
		center 40%,
		center 60%,
		center 80%,
		center 100%;
}
.chart-areas :where(tbody, tfoot) {
	width: 100%;
	display: flex;
	justify-content: stretch;
	align-items: stretch;
	flex-direction: row;
}
.chart-areas tbody tr:not(:nth-last-child(2)) {
	background-image: linear-gradient(var(--color-gray-800) 33%, transparent 0%);
	background-size: 1px 3px;
	background-repeat: repeat-y;
	background-position: right;
}
.chart-areas :where(tbody, tfoot) tr {
	position: relative;
	display: flex;
	flex-grow: 1;
	flex-shrink: 1;
	flex-basis: 0;
}
.chart-areas:not([data-subunit="hour"]) :where(tbody, tfoot) tr {
	cursor: pointer;
}
.chart-areas :where(tbody, tfoot) tr:last-child {
	width: 0;
	flex-grow: 0;
	white-space: nowrap;
}
.chart-areas tbody td {
	background: var(--color);
	opacity: 0.85;
	position: absolute;
	top: 0;
	right: 0;
	bottom: 0;
	left: 0;
	clip-path: polygon(
		0% calc(100% * (1 - var(--p0))),
		100% calc(100% * (1 - var(--p1))),
		100% calc(100% * (1 - var(--m1))),
		0% calc(100% * (1 - var(--m0)))
	);
	will-change: clip-path;
	transition: clip-path 0.3s ease-out;
}
.chart-areas tfoot {
	margin-top: var(--spacing-2);
	font-size: var(--text-xs);
	color: var(--color-text-light);
	text-align: center;
}

.chart-areas tfoot[data-less] tr:not(:nth-child(3n + 1), :last-child) {
	display: none;
}

.chart-areas tfoot td {
	transform: translateX(-50%);
}
.chart-areas tfoot [data-current] {
	color: #fff;
	font-weight: bold;
}

@container (min-width: 40rem) {
	.chart-areas {
		padding-inline-start: var(--spacing-12);
	}
}
</style>
