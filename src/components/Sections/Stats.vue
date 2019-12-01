<template>
  <div class="rt-stats">
    <k-view>
      <header class="k-field-header">
        <label class="k-field-label">
          <timeframe />
        </label>

        <prev-next />
      </header>

      <timeline />
    </k-view>
  </div>
</template>

<script>
import PrevNext from "../Navigation/PrevNext.vue";
import Timeframe from "../Navigation/Timeframe.vue";
import Timeline from "../Charts/Timeline.vue";

export default {
  components: {
    PrevNext,
    Timeframe,
    Timeline
  }
}
</script>

<style lang="scss">
.rt-stats {
  background: #2d2f36;
  color: rgba(#fff, .75);

  .k-view {
    margin-bottom: 1rem;
    padding-top: 1.5rem !important;
    padding-bottom: 2.5rem !important;
  }
}

.rt-stats-box {
  background: #3a3c45;
  padding: .75rem;
}
</style>

