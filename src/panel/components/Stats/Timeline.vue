<template>
	<div :data-subunit="subunit" class="k-retour-timeline">
		<table aria-hidden="true">
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
						:key="areaIndex"
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
import { axis, bounds, clip, max } from "../../helpers/chart";
import { format, subunit } from "../../helpers/unit";

export default {
	props: {
		data: Array,
		timespan: Object,
	},
	computed: {
		axisY() {
			return axis(this.max);
		},
		bounds() {
			return bounds(this.data);
		},
		format() {
			return format(this.timespan.unit);
		},
		max() {
			return max(this.data);
		},
		subunit() {
			return subunit(this.timespan.unit);
		},
	},
	methods: {
		clip(segment, area) {
			return clip(
				this.bounds[segment][area],
				this.bounds[segment + 1]?.[area],
				this.max,
			);
		},
		color(segment, area) {
			const next = this.data[segment + 1];

			if (next && next.label > this.now(next.label)) {
				return "transparent";
			}

			return area.color;
		},
		isCurrent(segment) {
			return segment.label === this.now(segment.label);
		},
		label(segment) {
			// parsing hours would skip or repeat one on DST changes
			if (this.subunit === "hour") {
				return segment.label.slice(-2);
			}

			return this.$library.dayjs(segment.label).format(this.format);
		},
		now(label) {
			// in the format of the label (month, day or hour) to compare
			// both as strings; not cached, as the view stays mounted
			// across reloads and can be open past midnight
			return this.$library
				.dayjs()
				.format("YYYY-MM-DD HH")
				.slice(0, label.length);
		},
		zoom(segment) {
			if (this.subunit === "hour") {
				return;
			}

			const date = this.$library.dayjs(segment.label);

			this.$panel.reload({
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
.k-retour-timeline {
	/* the clip paths and the y-axis are positioned physically */
	direction: ltr;
	overflow-x: auto;
	overflow-y: hidden;
	padding-block-start: var(--spacing-8);
	padding-inline: var(--spacing-3);
}

.k-retour-timeline table {
	--height: 250px;

	position: relative;
	width: calc(100% - 3rem);
	height: var(--height);
	margin-inline-start: 2rem;
	margin-inline-end: 1rem;
	border-collapse: collapse;
	border-spacing: 0;
}
.k-retour-timeline thead {
	position: absolute;
	top: 0;
	right: 100%;
	height: var(--height);
}
.k-retour-timeline thead tr {
	display: flex;
	height: var(--height);
	flex-direction: column-reverse;
	padding-right: var(--spacing-3);
}
.k-retour-timeline th {
	flex: 1;
	transform: translateY(calc(-1 * var(--spacing-3) / 2));
	font-size: var(--text-xs);
	font-weight: normal;
	text-align: right;
	color: var(--color-gray-500);
}
.k-retour-timeline tbody {
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
.k-retour-timeline :where(tbody, tfoot) {
	width: 100%;
	display: flex;
	justify-content: stretch;
	align-items: stretch;
	flex-direction: row;
}
.k-retour-timeline tbody tr:not(:nth-last-child(2)) {
	background-image: linear-gradient(var(--color-gray-800) 33%, transparent 0%);
	background-size: 1px 3px;
	background-repeat: repeat-y;
	background-position: right;
}
.k-retour-timeline :where(tbody, tfoot) tr {
	position: relative;
	display: flex;
	flex-grow: 1;
	flex-shrink: 1;
	flex-basis: 0;
	/* keeps the labels from widening their columns */
	width: 0;
}
.k-retour-timeline:not([data-subunit="hour"]) :where(tbody, tfoot) tr {
	cursor: pointer;
}
.k-retour-timeline :where(tbody, tfoot) tr:last-child {
	flex-grow: 0;
}
.k-retour-timeline tbody td {
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
	transition: clip-path 0.3s ease-out;
}
.k-retour-timeline tfoot {
	margin-top: var(--spacing-2);
	font-size: var(--text-xs);
	color: var(--color-gray-500);
	text-align: center;
}

/* every third label, counted from the last one to not overlap it */
.k-retour-timeline tfoot[data-less="true"] tr:not(:nth-last-child(3n + 1)) td {
	visibility: hidden;
}

.k-retour-timeline tfoot td {
	transform: translateX(-50%);
	white-space: nowrap;
}
.k-retour-timeline tfoot [data-current="true"] {
	color: var(--color-white);
	font-weight: bold;
}

@container (min-width: 40rem) {
	.k-retour-timeline {
		padding-inline-start: var(--spacing-12);
	}
}
</style>
