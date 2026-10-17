<template>
	<div class="k-retour-timeline">
		<div>
			<ol aria-hidden="true">
				<li v-for="tick in [...axisY].reverse()" :key="tick">
					<span>{{ tick }}</span>
				</li>
			</ol>

			<div
				v-for="(area, index) in data[0]?.areas"
				:key="index"
				:style="{ background: area.color, clipPath: polygon(index) }"
			/>

			<nav ref="plot" @resize="width = $event.detail.width">
				<k-link
					v-for="(segment, index) in data"
					:key="segment.label"
					:aria-current="isCurrent(segment) ? 'date' : null"
					:aria-label="title(segment)"
					:tabindex="isLabeled(index) ? null : -1"
					:to="link(segment)"
				>
					<span v-show="isLabeled(index)">{{ label(segment) }}</span>
				</k-link>
			</nav>
		</div>
	</div>
</template>

<script>
import { axis, bounds, max, polygon } from "../../helpers/chart";
import { format, subunit } from "../../helpers/unit";

export default {
	props: {
		data: Array,
		timespan: Object,
	},
	data() {
		return {
			width: 0,
		};
	},
	computed: {
		axisY() {
			return axis(this.max, this.$library.dayjs.locale());
		},
		bounds() {
			return bounds(this.data);
		},
		format() {
			return format(this.timespan.unit);
		},
		last() {
			// the areas end today, later segments lie in the future
			const index = this.data.findLastIndex(
				(segment) => segment.label <= this.now(segment.label),
			);

			return Math.max(index, 0);
		},
		max() {
			return max(this.data);
		},
		step() {
			// every nth label, so that they don't overlap (about 8px
			// per character plus some space), counted from the last one
			const chars = Math.max(
				...this.data.map((segment) => this.label(segment).length),
			);
			const space = this.width / Math.max(this.data.length - 1, 1);

			return Math.max(Math.ceil((chars * 8 + 6) / space), 1);
		},
		subunit() {
			return subunit(this.timespan.unit);
		},
	},
	mounted() {
		this.$panel.observers.resize.observe(this.$refs.plot);
	},
	beforeUnmount() {
		this.$panel.observers.resize.unobserve(this.$refs.plot);
	},
	methods: {
		isCurrent(segment) {
			return segment.label === this.now(segment.label);
		},
		isLabeled(index) {
			return (this.data.length - 1 - index) % this.step === 0;
		},
		label(segment) {
			// parsing hours would skip or repeat one on DST changes
			if (this.subunit === "hour") {
				return segment.label.slice(-2);
			}

			return this.$library.dayjs(segment.label).format(this.format);
		},
		link(segment) {
			// hours are the smallest unit
			if (this.subunit === "hour") {
				return null;
			}

			const date = this.$library.dayjs(segment.label);
			const query = new URLSearchParams({
				...this.$panel.view.query,
				from: date.startOf(this.subunit).format("YYYY-MM-DD"),
				to: date.endOf(this.subunit).format("YYYY-MM-DD"),
			});

			return `/${this.$panel.view.path}?${query}`;
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
		polygon(area) {
			// the same number of points for all timespans of up to 100
			// hours, days or months, so their shapes animate into each other
			const points = Math.max(this.data.length, 100);
			return polygon(this.bounds, area, this.max, this.last, points);
		},
		title(segment) {
			const format = this.subunit === "month" ? "MMMM YYYY" : "D MMMM YYYY";
			return this.$library.dayjs(segment.label).format(format);
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
	font-size: var(--text-xs);
	line-height: 1;
	color: var(--color-gray-500);
}
/* the ticks in a column as wide as the widest one, the plot next to it */
.k-retour-timeline > div {
	display: grid;
	grid-template-columns: auto 1fr;
	grid-template-rows: 250px;
	margin-inline-end: var(--spacing-4);
	margin-block-end: calc(var(--spacing-2) + 1em);
}

/* y-axis: a tick and its dotted line for each fifth */
.k-retour-timeline ol {
	grid-area: 1 / 1 / 2 / 3;
	display: grid;
	grid-template-columns: subgrid;
	grid-template-rows: repeat(5, 1fr);
}
.k-retour-timeline li {
	grid-column: 1 / 3;
	display: grid;
	grid-template-columns: subgrid;
}
.k-retour-timeline li span {
	align-self: start;
	padding-inline-end: var(--spacing-3);
	text-align: end;
	transform: translateY(-50%);
	white-space: nowrap;
}
.k-retour-timeline li::after {
	content: "";
	background: linear-gradient(
			to right,
			var(--color-gray-800) 33%,
			transparent 0%
		)
		top / 3px 1px repeat-x;
}
.k-retour-timeline li:first-child::after {
	background-size: 1px 1px;
}

/* one shape per area across all segments, as adjacent
   shapes would leave seams between them */
.k-retour-timeline > div > div {
	grid-area: 1 / 2;
	z-index: 1;
	opacity: 0.85;
	pointer-events: none;
	transition: clip-path 0.3s ease-out;
}

/* x-axis: a column per segment, with a dotted line and its label */
.k-retour-timeline nav {
	grid-area: 1 / 2;
	display: flex;
	/* bottom line */
	background: linear-gradient(
			to right,
			var(--color-gray-800) 33%,
			transparent 0%
		)
		bottom / 1px 1px repeat-x;
}
.k-retour-timeline nav .k-link {
	position: relative;
	flex: 1 1 0;
	/* keeps the labels from widening their columns */
	width: 0;
	background: linear-gradient(var(--color-gray-800) 33%, transparent 0%) left /
		1px 3px repeat-y;
	outline: 0;
}
.k-retour-timeline nav .k-link:is(:first-child, :last-child) {
	background: none;
}
.k-retour-timeline nav .k-link:last-child {
	flex-grow: 0;
}
.k-retour-timeline .k-link > span {
	position: absolute;
	top: calc(100% + var(--spacing-2));
	transform: translateX(-50%);
	white-space: nowrap;
}
.k-retour-timeline .k-link:focus-visible > span {
	outline: var(--outline);
	outline-offset: 2px;
	border-radius: var(--rounded-xs);
}
.k-retour-timeline [aria-current] > span {
	color: var(--color-white);
	font-weight: bold;
}
</style>
