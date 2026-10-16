<template>
	<k-button-group layout="collapsed" class="k-retour-timespan">
		<k-button
			:dropdown="true"
			:text="timespan.label"
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
			:disabled="!timespan.hasPrev || timespan.isAll"
			:title="$t('retour.timespan.previous.label')"
			@click="navigate('subtract')"
		/>
		<k-button
			:disabled="!timespan.hasNext || timespan.isAll"
			:title="$t('retour.timespan.next.label')"
			icon="angle-right"
			size="sm"
			variant="filled"
			@click="navigate('add')"
		/>
	</k-button-group>
</template>

<script>
import { daysIntoWeek } from "../../helpers/unit";

export default {
	props: {
		timespan: Object,
	},
	computed: {
		dropdown() {
			return [
				...this.units.map((unit) => {
					if (unit === "all" && !this.timespan.hasAll) {
						return;
					}

					return {
						text: this.label(unit),
						icon: this.icon(unit),
						current: this.isCurrentUnit(unit),
						click: () => this.set(unit),
					};
				}),
				"-",
				{
					text: this.$t("today"),
					icon: "merge",
					click: () => this.set("today"),
				},
				{
					text: this.$t("retour.timespan.set.label"),
					icon: "calendar",
					click: () => this.$panel.dialog.open("retour/timespan"),
				},
			].filter(Boolean);
		},
		units() {
			return ["all", "year", "month", "week", "day"];
		},
	},
	methods: {
		endOf(date, unit) {
			if (unit === "week") {
				return this.startOf(date, unit).add(6, "day");
			}

			return date.endOf(unit);
		},
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
		navigate(method) {
			const unit = this.timespan.unit;
			const from = this.$library.dayjs(this.timespan.from);
			const to = this.$library.dayjs(this.timespan.to);

			// custom ranges move by their own length
			if (unit === "days" || unit === "months") {
				const step = unit === "days" ? "day" : "month";
				const length = to.startOf(step).diff(from.startOf(step), step) + 1;

				return this.update({
					from: from[method](length, step).startOf(step),
					to: to[method](length, step).endOf(step),
				});
			}

			this.update({
				from: this.startOf(from[method](1, unit), unit),
				to: this.endOf(to[method](1, unit), unit),
			});
		},
		set(unit) {
			if (unit === "all") {
				return this.update({
					from: this.$library.dayjs(this.timespan.first),
					to: this.$library.dayjs(this.timespan.last),
				});
			}

			let date = this.$library.dayjs(this.timespan.from);

			// on hitting the current unit again,
			// move to current timespan around today
			if (unit === this.timespan.unit || unit === "today") {
				unit = this.timespan.unit;
				date = this.$library.dayjs();
			}

			this.update({
				from: this.startOf(date, unit),
				to: this.endOf(date, unit),
			});
		},
		startOf(date, unit) {
			// dayjs starts weeks on Sunday, the Panel's calendar
			// on the first weekday of the user's language
			if (unit === "week") {
				const weekday = this.$panel.translation.weekday;
				return date.subtract(daysIntoWeek(date.day(), weekday), "day");
			}

			return date.startOf(unit);
		},
		update({ from, to }) {
			this.$panel.reload({
				query: {
					from: from.format("YYYY-MM-DD"),
					to: to.format("YYYY-MM-DD"),
				},
			});
		},
	},
};
</script>
