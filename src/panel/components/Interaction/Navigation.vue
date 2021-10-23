<template>
  <k-view class="retour-navigation">
    <header class="k-header">
      <k-bar class="k-header-buttons">
        <template #left>
          <dates :dates="dates" :timespan="timespan" @navigate="navigate" />
        </template>
        <template #right>
          <prev-next :dates="dates" :timespan="timespan" @navigate="navigate" />
        </template>
      </k-bar>

      <k-tabs :tab="tab" :tabs="tabs" />
    </header>
  </k-view>
</template>

<script>
import Dates from "./Dates.vue";
import PrevNext from "./PrevNext.vue";

export default {
  components: {
    Dates,
    PrevNext
  },
  props: {
    tab: String,
    tabs: Array,
    timespan: Object
  },
  computed: {
    dates() {
      return {
        from: this.$library.dayjs(this.timespan.from),
        to:   this.$library.dayjs(this.timespan.to)
      };
    },
  },
  methods: {
    navigate(timespan) {
      this.$reload({
        query: {
          from: timespan.from.format("YYYY-MM-DD"),
          to: timespan.to.format("YYYY-MM-DD")
        }
      });
    }
  }
};
</script>

<style>
.retour-navigation .k-tab-button[title="Routes"] .k-tabs-badge {
  background: var(--color-blue-300);
}
.retour-navigation .k-tab-button[title="Failures"] .k-tabs-badge {
  background: var(--color-red-300);
}
</style>
