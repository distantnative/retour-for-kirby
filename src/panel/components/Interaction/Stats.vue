<template>
  <k-view class="retour-stats">
    <pie :data="pie" />
    <timeline :data="areas" :timespan="timespan" />
  </k-view>
</template>

<script>
import Pie from "../Graphs/Pie.vue";
import Timeline from "../Graphs/Timeline.vue";

export default {
  components: {
    Pie,
    Timeline
  },
  props: {
    data: Array,
    timespan: Object
  },
  computed: {
    areas() {
      return this.data.map(entry => {
        return {
          label: entry.date,
          areas: [
            {
              data: entry.redirected,
              color: "var(--color-focus)",
            },
            {
              data: entry.resolved,
              color: "var(--color-border)",
            },
            {
              data: entry.failed,
              color: "var(--color-negative)",
            }
          ]
        };
      });
    },
    pie() {
      return [
        {
          data: this.data.reduce((i, x) => i += x.redirected, 0),
          color: "var(--color-focus)",
          label: this.$t("retour.stats.redirected")
        },
        {
          data: this.data.reduce((i, x) => i += x.resolved, 0),
          color: "var(--color-border)",
          label: this.$t("retour.stats.resolved")
        },
        {
          data: this.data.reduce((i, x) => i += x.failed, 0),
          color: "var(--color-negative)",
          label: this.$t("retour.stats.failed")
        }
      ];
    }
  }
};
</script>

<style>
.retour-stats {
  display: grid;
  grid-template-columns: 1fr;
  gap: 2rem;
  padding-top: 2.5rem;
  padding-bottom: 2.5rem;
  background: #2b2b2b;
  color: var(--color-white);
}

@media screen and (min-width: 45em) {
  .retour-stats {
    grid-template-columns: 1fr 3fr;
  }
}
</style>
