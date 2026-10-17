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
			:link="link(timespan.prev)"
			:title="$t('retour.timespan.previous.label')"
		/>
		<k-button
			:disabled="!timespan.next"
			:link="link(timespan.next)"
			:title="$t('retour.timespan.next.label')"
			icon="angle-right"
			size="sm"
			variant="filled"
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
				...(this.timespan.units.all ? [this.unit("all"), "-"] : []),
				this.unit("year"),
				this.unit("month"),
				this.unit("week"),
				this.unit("day"),
				{
					text: this.$t("custom"),
					icon: "calendar",
					current:
						this.timespan.isAll === false &&
						["days", "months"].includes(this.timespan.unit),
					click: () =>
						this.$panel.dialog.open("retour/timespan", {
							on: {
								// the date fields submit their values with a time
								submit: ({ from, to }) => {
									this.$panel.dialog.close();
									this.$panel.view.open(
										this.link({ from: from.slice(0, 10), to: to.slice(0, 10) }),
									);
								},
							},
						}),
				},
				"-",
				{
					text: this.$t("today"),
					icon: "merge",
					link: this.link(this.timespan.units.today),
				},
			];
		},
		text() {
			const { from, to, unit } = this.timespan;

			// months and years without days; `formatRange()` merges what both dates share
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
		link(timespan) {
			if (timespan) {
				const query = new URLSearchParams({
					...this.$panel.view.query,
					...timespan,
				});

				// another timespan starts on the first page
				query.delete("page");

				return `/${this.$panel.view.path}?${query}`;
			}

			return null;
		},
		unit(unit) {
			return {
				text: this.label(unit),
				icon: this.icon(unit),
				current: this.isCurrentUnit(unit),
				link: this.link(this.timespan.units[unit]),
			};
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
