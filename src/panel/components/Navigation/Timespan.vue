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

    <k-dropdown-content ref="units" :options="dropdown" align-x="end" />

    <k-button
      icon="angle-left"
      size="sm"
      variant="filled"
      :disabled="!timespan.hasPrev || timespan.isAll"
      @click="navigate('subtract')"
    />
    <k-button
      :disabled="!timespan.hasNext || timespan.isAll"
      icon="angle-right"
      size="sm"
      variant="filled"
      @click="navigate('add')"
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
          if (unit === "all" && !this.timespan.hasAll) {
            return;
          }

          return {
            text: this.$t("retour.stats.mode." + unit),
            icon: this.icon(unit),
            current: this.isCurrentUnit(unit),
            click: () => this.set(unit),
          };
        }),
        "-",
        {
          text: this.$t("retour.timespan.today.label"),
          icon: "merge",
          click: () => this.set("today"),
        },
        {
          text: this.$t("retour.timespan.set.label"),
          icon: "calendar",
          click: () => this.$dialog("retour/timespan"),
        }
      ].filter(Boolean);
    },
    units() {
      return ["all", "year", "month", "day"];
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
    navigate(method) {
      const unit = this.timespan.unit;
      const from = this.$library.dayjs(this.timespan.from);
      const to = this.$library.dayjs(this.timespan.to);

      this.update({
        from: from[method](1, unit).startOf(unit),
        to: to[method](1, unit).endOf(unit),
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
        from: date.startOf(unit),
        to: date.endOf(unit),
      });
    },
    update({ from, to }) {
      this.$reload({
        query: {
          from: from.format("YYYY-MM-DD"),
          to: to.format("YYYY-MM-DD"),
        },
      });
    },
  },
};
</script>
