<template>
  <div class="rt-stats">
    <k-view>
      <k-grid gutter="medium">
        <k-column width="1/4">
          <share />
        </k-column>

        <k-column width="3/4">
          <timeline v-on="$listeners" />
        </k-column>
      </k-grid>
    </k-view>
  </div>
</template>

<script>
import Timeline from "./../Charts/Timeline.vue";
import Share  from "./../Charts/Share.vue";

export default {
  components: {
    timeline: Timeline,
    share: Share
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

  [aria-current] {
    color: #fff;
    font-weight: 600;

    .k-button-text {
      opacity: 1;
    }
  }
}

.rt-stats-box {
  background: #3a3c45;
  padding: .75rem;
}

.rt-lb-failed .k-icon,
.ct-series-a .ct-area,
.ct-series-c .ct-slice-pie {
  color: #c82828;
  fill: #c82828;
  fill-opacity: .75;
}

.rt-lb-redirected .k-icon,
.ct-series-a .ct-slice-pie,
.rt-timeline .ct-series-c .ct-area {
  color: #4271ae;
  fill:#4271ae;
  fill-opacity: 1;
}

.ct-series-b .ct-slice-pie,
.rt-lb-resolved .k-icon,
.rt-timeline .ct-series-b .ct-area {
  color: #ddd;
  fill: #ddd;
  fill-opacity: 1;
}
</style>

