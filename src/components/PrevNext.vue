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
      const selection = {
        begin: this.$library.dayjs().startOf("day"),
        end:   this.$library.dayjs().endOf("day")
      };

      switch (by) {
        case "all":
          return this.$store.dispatch("retour/selection", "all");
        case "year":
          selection.begin = selection.begin.startOf("year");
          selection.end   = selection.end.endOf("year");
          break;
        case "month":
          selection.begin = selection.begin.startOf("month");
          selection.end   = selection.end.endOf("month");
          break;
        case "week":
          if (begin.day() === 0) {
            selection.begin = selection.begin.subtract(6, "day");
          } else {
            selection.begin = selection.begin.subtract(begin.day() - 1, "day");
            selection.end   = selection.end.add(7 - end.day(), "day");
          }
          break;
      }

      return this.$store.dispatch("retour/selection", selection);
    },
    onNavigate(method) {
      let factor = 1;
      let unit   = this.mode;

      if (this.mode === "week") {
        factor = 7;
        unit   = "day";
      }

      this.$store.dispatch("retour/selection", {
        begin: this.$store.state.retour.selection.begin[method](factor, unit).startOf(unit),
        end:   this.$store.state.retour.selection.end[method](factor, unit).startOf(unit)
      });
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
