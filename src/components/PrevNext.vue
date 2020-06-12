<template>
  <k-button-group>
    <k-button icon="angle-left" :disabled="hasPrev" @click="onPrev" />
    <k-button
      v-for="by in ['all', 'year', 'month', 'week', 'day']"
      :key="by"
      :current="selection === by"
      @click="show(by)"
    >
      {{ $t('retour.stats.' + by) }}
    </k-button>
    <k-button icon="angle-right" :disabled="hasNext" @click="onNext" />
  </k-button-group>
</template>

<script>
export default {
  computed: {
    hasNext() {
      return this.selection === false || this.selection === 'all';
    },
    hasPrev() {
      return this.selection === false || this.selection === 'all';
    },
    selection() {
      return this.$store.getters["retour/selection"];
    }
  },
  methods: {
    navigate(method) {
      const start = this.$store.state.retour.selection.from;
      const end = this.$store.state.retour.selection.to;

      switch (this.selection) {
        case "year":
          this.$store.dispatch("retour/selection", {
            from: start[method](1, "year"),
            to: end[method](1, "year")
          });
          break;
        case "month":
          this.$store.dispatch("retour/selection", {
            from: start[method](1, "month"),
            to: end[method](1, "month").endOf("month")
          });
          break;
        case "week":
          this.$store.dispatch("retour/selection", {
            from: start[method](7, "day"),
            to: end[method](7, "day")
          });
          break;
        case "day":
          this.$store.dispatch("retour/selection", {
            from: start[method](1, "day"),
            to: end[method](1, "day")
          });
          break;
      }
    },
    onPrev() {
      return this.navigate("subtract");
    },
    onNext() {
      return this.navigate("add");
    },
    show(by) {
      const start = this.$library.dayjs().startOf("day");
      const end   = this.$library.dayjs().endOf("day");

      switch (by) {
        case "all":
          this.$store.dispatch("retour/selection", "all");
          break;
        case "year":
          this.$store.dispatch("retour/selection", {
            from: start.startOf("year"),
            to: end.endOf("year")
          });
          break;
        case "month":
          this.$store.dispatch("retour/selection", {
            from: start.startOf("month"),
            to: end.endOf("month")
          });
          break;
        case "week":
          if (start.day() === 0) {
            this.$store.dispatch("retour/selection", {
              from: start.subtract(6, "day"),
              to: end
            });
          } else {
            this.$store.dispatch("retour/selection", {
              from: start.subtract(start.day() - 1, "day"),
              to: end.add(7 - end.day(), "day")
            });
          }
          break;
        case "day":
          this.$store.dispatch("retour/selection", {
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
