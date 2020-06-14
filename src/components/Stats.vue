<template>
  <div class="rt-stats bg-black-light text-white">
    <k-view class="pt-6 pb-8">
      <k-grid gutter="medium">
        <k-column width="1/4">
          <header class="k-header-bar flex items-center justify-between h-10">
            <retour-timeframe-dropdown />
          </header>
          <retour-chart class="retour-stats-box rounded-sm" />
        </k-column>

        <k-column width="3/4">
          <header class="k-header-bar flex items-center justify-between h-10">
            <k-button v-if="$config.debug" icon="refresh" @click="onRefresh" />
            <div v-else />
            <retour-prevnext />
          </header>
          <retour-timeline class="retour-stats-box rounded-sm" />
        </k-column>
      </k-grid>
    </k-view>
  </div>
</template>

<script>
import TimeframeDropdown from "./TimeframeDropdown.vue";
import PrevNext from "./PrevNext.vue";
import Chart from "./Chart.vue";
import Timeline from "./Timeline.vue";

export default {
  components: {
    "retour-timeframe-dropdown": TimeframeDropdown,
    "retour-prevnext": PrevNext,
    "retour-chart": Chart,
    "retour-timeline": Timeline,
  },
  methods: {
    onRefresh() {
      this.$store.dispatch("retour/load");
    }
  }
}
</script>

<style lang="scss">
.retour-stats-box {
  background: #3d3d3d;
}
</style>
