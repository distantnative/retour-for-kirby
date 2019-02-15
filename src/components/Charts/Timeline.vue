<template>
  <div>
    <header class="k-field-header">
      <label class="k-field-label" />

      <k-button-group>
        <k-button
          icon="angle-left"
          @click="$emit('navigate', [frame, offset - 1])"
        />
        <k-button
          v-for="by in ['month', 'week', 'day']"
          :key="by"
          :current="frame === by"
          @click="$emit('navigate', [by, 0])"
        >
          {{ $t('rt.dashboard.' + by) }}
        </k-button>
        <k-button
          icon="angle-right"
          :disabled="offset >= 0"
          @click="$emit('navigate', [frame, offset + 1])"
        />
      </k-button-group>
    </header>

    <div class="k-card k-card-content rt-timeline" />
  </div>
</template>

<script>
import Chartist from "chartist";

export default {
  props: {
    frame: String,
    offset: Number,
    data: Object
  },
  watch: {
    data() {
      this.createChart();
    }
  },
  created() {
    this.$events.$on("retour-go", (part) => {
      if (part === "dashboard") {
        this.createChart();
      }
    });
  },
  methods: {
    createChart() {
      new Chartist.Line(".rt-timeline", {
        labels: this.data.labels,
        series: [
          this.data.failed.map((x, i) => x + this.data.redirected[i]),
          this.data.redirected,
        ]
      }, {
        height: 220,
        showLabel: false,
        low: 0,
        showArea: true,
        showLine: false,
        showPoint: false,
        fullWidth: true,
        axisY: {
          onlyInteger: true
        }
      });
    }
  }
}
</script>

<style>
.rt-timeline > svg {
  margin-top: .75rem;
  margin-left: -.5rem;
}

.rt-timeline .ct-label.ct-horizontal.ct-end {
  display: block;
  transform: translateX(-50%);
  text-align: center;
}

.rt-timeline .ct-series-a .ct-area {
  fill: #ccc;
  fill-opacity: .5;
}
.rt-timeline .ct-series-b .ct-area {
  fill: #4271ae;
  fill-opacity: .75;
}
</style>
