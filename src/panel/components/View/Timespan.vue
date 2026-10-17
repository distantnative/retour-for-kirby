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
	mounted() {
		this.$events.on("keydown.left", this.toPrev);
		this.$events.on("keydown.right", this.toNext);
	},
	beforeUnmount() {
		this.$events.off("keydown.left", this.toPrev);
		this.$events.off("keydown.right", this.toNext);
	},
	methods: {
		link(timespan) {
			if (timespan) {
				const query = new URLSearchParams({
					...this.$panel.view.query,
					...timespan,
				});
				query.delete("page");

				return `/${this.$panel.view.path}?${query}`;
			}

			return null;
		},
		toNext(e) {
			if (this.timespan.next && e.target.localName === "body") {
				this.$go(this.link(this.timespan.next));
			}
		},
		toPrev(e) {
			if (this.timespan.prev && e.target.localName === "body") {
				this.$go(this.link(this.timespan.prev));
			}
		},
		unit(unit) {
			const current =
				unit === this.timespan.unit || (unit === "all" && this.timespan.isAll);
			let icon = "circle";

			if (current) {
				icon = this.timespan.isCurrent ? "circle-focus" : "circle-nested";
			}

			return {
				text: this.$t(
					["all", "week"].includes(unit) ? `retour.stats.mode.${unit}` : unit,
				),
				icon,
				current,
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
