<template>
  <div class="rt-stats">
    <k-view>
      <k-grid gutter="small">
        <k-column width="1/4">
          <header class="k-header-bar">
            <retour-timeframe />
          </header>
          <retour-chart />
        </k-column>

        <k-column width="3/4">
          <header class="k-header-bar">
            <k-button v-if="debug" icon="refresh" @click="onRefresh" />
            <div v-else />
            <retour-prevnext />
          </header>
          <retour-timeline />
        </k-column>
      </k-grid>
    </k-view>
  </div>
</template>

<script>
import Chart from "./Graphs/Chart.vue";
import Timeline from "./Graphs/Timeline.vue";

import Timeframe from "./Interaction/Timeframe.vue";
import PrevNext from "./Interaction/PrevNext.vue";

export default {
  components: {
    "retour-timeframe": Timeframe,
    "retour-prevnext": PrevNext,
    "retour-chart": Chart,
    "retour-timeline": Timeline,
  },
  computed: {
    debug() {
      return window.panel.debug;
    }
  },
  methods: {
    onRefresh() {
      this.$store.dispatch("retour/load");
    }
  }
}
</script>

<style lang="scss">
.rt-stats {
  background: #2b2b2b;
  color: #fff;
}
.rt-stats > .k-view {
  padding-top: 1.5rem;
  padding-bottom: 2rem;
}
.rt-stats .k-header-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.rt-stats .k-header-bar .k-button-group {
  margin: 0;
}
</style>
