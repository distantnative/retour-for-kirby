<template>
  <header class="retour-navigation">
    <k-tabs :tab="tab" :tabs="tabs" />

    <k-bar>
      <dates :dates="dates" :timespan="timespan" @navigate="navigate" />
      <prev-next :dates="dates" :timespan="timespan" @navigate="navigate" />
    </k-bar>
  </header>
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
    tab: String,
    tabs: Array,
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
.retour-navigation {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--spacing-8);
}
.retour-navigation .k-tabs {
  margin-bottom: 0;
}
.retour-navigation .k-tab-button[title="Routes"] .k-tabs-badge {
  background: var(--color-blue-300);
}
.retour-navigation .k-tab-button[title="Failures"] .k-tabs-badge {
  background: var(--color-red-300);
}

.retour-navigation .k-bar {
  gap: var(--spacing-4);
}
</style>
