<template>
  <k-button-group>
    <k-button icon="angle-left" :disabled="hasPrev" @click="onPrev" />
    <k-button
      v-for="by in ['all', 'year', 'month', 'week', 'day']"
      :key="by"
      :current="mode === by"
      @click="show(by)"
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
    navigate(method) {
      const begin = this.$store.state.retour.selection.begin;
      const end   = this.$store.state.retour.selection.end;

      switch (this.mode) {
        case "year":
          this.$store.dispatch("retour/selection", {
            begin: begin[method](1, "year"),
            end:   end[method](1, "year")
          });
          break;
        case "month":
          this.$store.dispatch("retour/selection", {
            begin: begin[method](1, "month"),
            end:   end[method](1, "month").endOf("month")
          });
          break;
        case "week":
          this.$store.dispatch("retour/selection", {
            begin: begin[method](7, "day"),
            end:   end[method](7, "day")
          });
          break;
        case "day":
          this.$store.dispatch("retour/selection", {
            begin: begin[method](1, "day"),
            end:   end[method](1, "day")
          });
          break;
      }
    },
    onNext() {
      return this.navigate("add");
    },
    onPrev() {
      return this.navigate("subtract");
    },
    show(by) {
      const begin = this.$library.dayjs().startOf("day");
      const end   = this.$library.dayjs().endOf("day");

      switch (by) {
        case "all":
          this.$store.dispatch("retour/selection", "all");
          break;
        case "year":
          this.$store.dispatch("retour/selection", {
            begin: begin.startOf("year"),
            end:   end.endOf("year")
          });
          break;
        case "month":
          this.$store.dispatch("retour/selection", {
            begin: begin.beginOf("month"),
            end:   end.endOf("month")
          });
          break;
        case "week":
          if (begin.day() === 0) {
            this.$store.dispatch("retour/selection", {
              begin: begin.subtract(6, "day"),
              end:   end
            });
          } else {
            this.$store.dispatch("retour/selection", {
              begin: begin.subtract(begin.day() - 1, "day"),
              end:   end.add(7 - end.day(), "day")
            });
          }
          break;
        case "day":
          this.$store.dispatch("retour/selection", {
            begin: begin,
            end:   end
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
