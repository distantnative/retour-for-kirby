<template>
  <div class="rt-stats">
    <k-view>
      <header class="k-field-header">
        <label class="k-field-label">
          <timeframe />
        </label>

        <prev-next />
      </header>

      <k-grid gutter="medium">
        <k-column width="1/4">
          <pie />
        </k-column>

        <k-column width="3/4">
          <timeline />
        </k-column>
      </k-grid>
    </k-view>
  </div>
</template>

<script>
import PrevNext from "../Navigation/PrevNext.vue";
import Timeframe from "../Navigation/Timeframe.vue";

import Pie from "../Charts/Pie.vue";
import Timeline from "../Charts/Timeline.vue";

export default {
  components: {
    PrevNext,
    Timeframe,
    Pie,
    Timeline
  }
};
</script>

<style lang="scss">
.rt-stats {
  background: #2d2f36;
  color: rgba(#fff, 0.75);

  .k-view {
    margin-bottom: 1rem;
    padding-top: 1.5rem !important;
    padding-bottom: 2.5rem !important;
  }

  @media screen and (max-width: 40em) {
    .k-field-header {
      display: block;
    }

    .k-button-group {
      position: relative;
      top: -0.25rem;
    }
  }
}

.rt-stats-box {
  background: #3a3c45;
  padding: 0.75rem;
}

.rt-lb-failed .k-icon,
.ct-series-a .ct-area,
.ct-series-c .ct-slice-pie {
  color: var(--color-negative);
  fill: var(--color-negative);
  fill-opacity: 0.85;
}

.rt-lb-redirected .k-icon,
.ct-series-a .ct-slice-pie,
.rt-timeline .ct-series-e .ct-area {
  color: var(--color-focus);
  fill: var(--color-focus);
  fill-opacity: 0.85;
}

.ct-series-b .ct-slice-pie,
.rt-lb-resolved .k-icon,
.rt-timeline .ct-series-c .ct-area {
  color: var(--color-border);
  fill: var(--color-border);
  fill-opacity: 0.85;
}
</style>
