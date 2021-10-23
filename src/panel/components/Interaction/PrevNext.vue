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
      @click="set(unit)"
    >
      {{ $t('retour.stats.mode.' + unit) }}
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
    timespan: Object
  },
  computed: {
    hasPrev() {
      return this.dates.from.isAfter(this.$library.dayjs(this.timespan.first));
    },
    hasNext() {
      return this.dates.to.isBefore(this.$library.dayjs(this.timespan.first)) || this.dates.to.isBefore(this.$library.dayjs());
    },
    isAll() {
      return this.dates.from.isSame(this.$library.dayjs(this.timespan.first), "day") && this.dates.to.isSame(this.$library.dayjs(this.timespan.last), "day");
    }
  },
  methods: {
    isCurrent(unit) {
      if (unit === "all") {
        return this.isAll;
      }

      return unit === this.timespan.unit;
    },
    set(unit) {
      let timespan = Object.assign({}, this.dates);

      if (unit === this.timespan.unit) {
        timespan = {
          from: this.$library.dayjs().clone(),
          to: this.$library.dayjs().clone(),
        };
      }

      switch (unit) {
      case "all":
        timespan.from  = this.$library.dayjs(this.timespan.first);
        timespan.to    = this.$library.dayjs(this.timespan.last);
        break;
      default:
        timespan.from = timespan.from.startOf(unit);
        timespan.to   = timespan.from.endOf(unit);
        break;
      }

      this.$emit("navigate", timespan);
    },
    onNavigate(method) {
      let unit   = this.timespan.unit;
      let factor = 1;

      if (unit === "week") {
        factor = 7;
        unit   = "day";
      }

      const timespan = this.dates;
      timespan.from  = timespan.from[method](factor, unit).startOf(unit);
      timespan.to    = timespan.to[method](factor, unit).endOf(unit);
      this.$emit("navigate", timespan);
    }
  }
}
</script>

<style>
.retour .prevnext [aria-current] {
  font-weight: 600;
}
.retour .prevnext .k-button-text {
  opacity: 1;
}
</style>
