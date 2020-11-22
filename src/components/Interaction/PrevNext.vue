<template>
  <k-button-group>
    <k-button icon="angle-left" :disabled="hasPrev" @click="onPrev" />
    <k-button
      v-for="by in ['all', 'year', 'month', 'week', 'day']"
      :key="by"
      :current="mode === by"
      @click="onMode(by)"
    >
      {{ $t('retour.stats.mode.' + by) }}
    </k-button>
    <k-button icon="angle-right" :disabled="hasNext" @click="onNext" />
  </k-button-group>
</template>

<script>
export default {
  computed: {
    hasNext() {
      return this.mode === false || this.mode === 'all';
    },
    hasPrev() {
      return this.mode === false || this.mode === 'all';
    },
    mode() {
      return this.$store.getters["retour/mode"];
    }
  },
  methods: {
    onMode(by) {
      const view = [
        this.$library.dayjs().startOf("day"),
        this.$library.dayjs().endOf("day")
      ];

      switch (by) {
        case "all":
          return this.$store.dispatch("retour/view", "all");
        case "year":
          view[0] = view[0].startOf("year");
          view[1] = view[1].endOf("year");
          break;
        case "month":
          view[0] = view[1].startOf("month");
          view[1] = view[1].endOf("month");
          break;
        case "week":
          if (view[0].day() === 0) {
            view[0] = view[0].subtract(6, "day");
          } else {
            view[0] = view[0].subtract(view[0].day() - 1, "day");
            view[1] = view[1].add(7 - view[1].day(), "day");
          }
          break;
      }

      return this.$store.dispatch("retour/view", view);
    },
    onNavigate(method) {
      let factor = 1;
      let unit   = this.mode;

      if (this.mode === "week") {
        factor = 7;
        unit   = "day";
      }

      let dates = this.$store.getters["retour/dates"];
      dates = dates.map(date => date[method](factor, unit));

      this.$store.dispatch("retour/view", [
        dates[0].startOf(unit),
        dates[1].endOf(unit)
      ]);
    },
    onNext() {
      return this.onNavigate("add");
    },
    onPrev() {
      return this.onNavigate("subtract");
    }
  }
}
</script>

<style lang="scss">
.rt-stats [aria-current] {
  color: #fff;
  font-weight: 600;

  .k-button-text {
    opacity: 1;
  }
}
</style>
