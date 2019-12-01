<template>
  <k-button-group>
    <k-button icon="angle-left" :disabled="view === false" @click="prev" />
    <k-button
      v-for="by in ['year', 'month', 'week', 'day']"
      :key="by"
      :current="view === by"
      @click="show(by)"
    >
      {{ $t('rt.stats.' + by) }}
    </k-button>
    <k-button icon="angle-right" :disabled="view === false" @click="next" />
  </k-button-group>
</template>

<script>
export default {
  computed: {
    view() {
      return this.$store.getters["retour/view"];
    }
  },
  methods: {
    prev() {
      return this.navigate("subtract");
    },
    next() {
      return this.navigate("add");
    },
    navigate(method) {
      const start = this.$store.state.retour.view.from;
      const end   =  this.$store.state.retour.view.to;

      switch (this.view) {
        case "year":
          this.$store.dispatch("retour/timeframe", {
            from: start[method](1, "year"),
            to: end[method](1, "year")
          });
          break;
        case "month":
          this.$store.dispatch("retour/timeframe", {
            from: start[method](1, "month"),
            to: end[method](1, "month").endOf("month")
          });
          break;
        case "week":
          this.$store.dispatch("retour/timeframe", {
            from: start[method](7, "day"),
            to: end[method](7, "day")
          });
          break;
        case "day":
          this.$store.dispatch("retour/timeframe", {
            from: start[method](1, "day"),
            to: end[method](1, "day")
          });
          break;
      }
    },
    show(by) {
      const start = this.$library.dayjs().startOf("day");
      const end   = this.$library.dayjs().endOf("day");

      switch (by) {
        case "year":
          this.$store.dispatch("retour/timeframe", {
            from: start.startOf("year"),
            to: end.endOf("year")
          });
          break;
        case "month":
          this.$store.dispatch("retour/timeframe", {
            from: start.startOf("month"),
            to: end.endOf("month")
          });
          break;
        case "week":
          if (start.day() === 0) {
            this.$store.dispatch("retour/timeframe", {
              from: start.subtract(6, "day"),
              to: end
            });
          } else {
            this.$store.dispatch("retour/timeframe", {
              from: start.subtract(start.day() - 1, "day"),
              to: end.add(7 - end.day(), "day")
            });
          }
          break;
        case "day":
          this.$store.dispatch("retour/timeframe", {
            from: start,
            to: end
          });
          break;
      }
    }
  }
}
</script>

<style lang="scss">
.rt-stats {
  [aria-current] {
    color: #fff;
    font-weight: 600;

    .k-button-text {
      opacity: 1;
    }
  }
}
</style>
