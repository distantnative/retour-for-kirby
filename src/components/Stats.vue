<template>
  <div class="rt-stats bg-black-light text-white">
    <k-view class="mb-6 pt-6 pb-8">

      <header class="k-header-bar flex items-center justify-between h-10">
        <retour-timeframe-dropdown />
        <retour-prevnext />
      </header>

      <k-grid gutter="medium">
        <k-column width="1/4" class="retour-stats-box rounded-sm">
          <retour-chart />
        </k-column>

        <k-column width="3/4" class="retour-stats-box rounded-sm">
          <retour-timeline />
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
  }
}
</script>

<style lang="scss">
.retour-stats-box {
  background: #3d3d3d;
}
</style>
