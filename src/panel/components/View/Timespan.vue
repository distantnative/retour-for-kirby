<template>
	<k-button-group layout="collapsed" class="k-retour-timespan">
		<k-button
			:dropdown="true"
			:text="text"
			icon="calendar"
			size="sm"
			variant="filled"
			@click="$refs.units.toggle()"
		/>

		<k-dropdown ref="units" :options="dropdown" align-x="end" />

		<k-button
			icon="angle-left"
			size="sm"
			variant="filled"
			:disabled="!timespan.prev"
			:title="$t('retour.timespan.previous.label')"
			@click="update(timespan.prev)"
		/>
		<k-button
			:disabled="!timespan.next"
			:title="$t('retour.timespan.next.label')"
			icon="angle-right"
			size="sm"
			variant="filled"
			@click="update(timespan.next)"
		/>
	</k-button-group>
</template>

<script>
export default {
	props: {
		timespan: Object,
	},
	computed: {
		dropdown() {
			return [
				...this.units.map((unit) => {
					if (!this.timespan.units[unit]) {
						return;
					}

					return {
						text: this.label(unit),
						icon: this.icon(unit),
						current: this.isCurrentUnit(unit),
						click: () => this.update(this.timespan.units[unit]),
					};
				}),
				"-",
				{
					text: this.$t("today"),
					icon: "merge",
					click: () => this.update(this.timespan.units.today),
				},
				{
					text: this.$t("retour.timespan.set.label"),
					icon: "calendar",
					click: () => this.$panel.dialog.open("retour/timespan"),
				},
			].filter(Boolean);
		},
		text() {
			const { from, to, unit } = this.timespan;

			// whole months and years only name those, other timespans
			// their days; `formatRange()` merges what both dates share
			const options = {
				day: { day: "numeric", month: "long" },
				month: { month: "long" },
				year: {},
			}[unit] ?? { day: "numeric", month: "short" };

			return new Intl.DateTimeFormat(this.$library.dayjs.locale(), {
				...options,
				year: "numeric",
				// like the Panel's dates, e.g. not Persian ones for `fa`
				calendar: "gregory",
				numberingSystem: "latn",
				timeZone: "UTC",
			}).formatRange(new Date(from), new Date(to));
		},
		units() {
			return ["all", "year", "month", "week", "day"];
		},
	},
	methods: {
		icon(unit) {
			if (this.isCurrentUnit(unit) === true) {
				return this.timespan.isCurrent ? "circle-focus" : "circle-nested";
			}

			return "circle";
		},
		isCurrentUnit(unit) {
			return (
				unit === this.timespan.unit || (unit === "all" && this.timespan.isAll)
			);
		},
		label(unit) {
			// Kirby has no translations for these units
			if (unit === "all" || unit === "week") {
				return this.$t(`retour.stats.mode.${unit}`);
			}

			return this.$t(unit);
		},
		update({ from, to }) {
			this.$panel.reload({ query: { from, to } });
		},
	},
};
</script>

<style>
/* long labels wrap instead of pushing the buttons off narrow screens */
.k-retour-timespan {
	min-width: 0;
}
.k-retour-timespan > .k-button:first-child {
	flex-shrink: 1;
	min-width: 0;
	height: auto;
	min-height: var(--height-sm);
	padding-block: var(--spacing-1);
}
.k-retour-timespan > .k-button:first-child .k-button-text {
	white-space: normal;
	text-wrap: balance;
	line-height: 1.25;
}
</style>
