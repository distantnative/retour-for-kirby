<template>
  <k-bar class="k-retour-timespan">
    <dates :dates="dates" :timespan="timespan" @navigate="navigate" />
    <prev-next :dates="dates" :timespan="timespan" @navigate="navigate" />
  </k-bar>
</template>

<script>
import Dates from "./Dates.vue";
import PrevNext from "./PrevNext.vue";

export default {
  components: {
    Dates,
    PrevNext,
  },
  props: {
    timespan: Object,
  },
  computed: {
    dates() {
      return {
        from: this.$library.dayjs(this.timespan.from),
        to: this.$library.dayjs(this.timespan.to),
      };
    },
  },
  methods: {
    navigate(timespan) {
      this.$reload({
        query: {
          from: timespan.from.format("YYYY-MM-DD"),
          to: timespan.to.format("YYYY-MM-DD"),
        },
      });
    },
  },
};
</script>

<style>
.k-retour-timespan {
  gap: var(--spacing-4);
}
</style>
