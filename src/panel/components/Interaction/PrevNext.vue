<template>
  <k-button-group class="prevnext">
    <k-button
      icon="angle-left"
      :disabled="!hasPrev || isAll"
      @click="onNavigate('subtract')"
    />

    <k-button
      v-for="unit in ['all', 'year', 'month', 'day']"
      :key="unit"
      :current="isCurrent(unit)"
      :disabled="isDisabled(unit)"
      @click="set(unit)"
    >
      {{ $t("retour.stats.mode." + unit) }}
    </k-button>

    <k-button
      icon="angle-right"
      :disabled="!hasNext || isAll"
      @click="onNavigate('add')"
    />
  </k-button-group>
</template>

<script>
export default {
  props: {
    dates: Object,
    timespan: Object,
  },
  computed: {
    first() {
      return this.$library.dayjs(this.timespan.first);
    },
    last() {
      return this.$library.dayjs(this.timespan.last);
    },
    hasPrev() {
      return this.dates.from.isAfter(this.first);
    },
    hasNext() {
      // either has more data in the future
      // or today is in the future
      return (
        this.dates.to.isBefore(this.last) ||
        this.dates.to.isBefore(this.$library.dayjs())
      );
    },
    isAll() {
      return (
        this.dates.from.isSame(this.first, "day") &&
        this.dates.to.isSame(this.last, "day")
      );
    },
  },
  methods: {
    isCurrent(unit) {
      if (unit === "all") {
        return this.isAll;
      }

      return unit === this.timespan.unit;
    },
    isDisabled(unit) {
      return unit === "all" && (!this.timespan.first || !this.timespan.last);
    },
    set(unit) {
      if (unit === "all") {
        return this.$emit("navigate", {
          from: this.first,
          to: this.last,
        });
      }

      let timespan = Object.assign({}, this.dates);

      // on hitting the current unit again,
      // move to current timespan around today
      if (unit === this.timespan.unit) {
        timespan = {
          from: this.$library.dayjs(),
          to: this.$library.dayjs(),
        };
      }

      this.$emit("navigate", {
        from: timespan.from.startOf(unit),
        to: timespan.from.endOf(unit),
      });
    },
    onNavigate(method) {
      let unit = this.timespan.unit;
      let factor = 1;

      if (unit === "week") {
        factor = 7;
        unit = "day";
      }

      this.$emit("navigate", {
        from: this.dates.from[method](factor, unit).startOf(unit),
        to: this.dates.to[method](factor, unit).endOf(unit),
      });
    },
  },
};
</script>

<style>
.retour .prevnext [aria-current] {
  font-weight: 600;
}
.retour .prevnext .k-button-text {
  opacity: 1;
}
</style>
