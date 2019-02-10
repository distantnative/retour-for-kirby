<template>
  <div>
    <header class="k-field-header">
      <label class="k-field-label" />

      <k-button-group>
        <k-button
          icon="angle-left"
          @click="$emit('navigate', [stats.frame, stats.offset - 1])"
        />
        <k-button
          icon="calendar"
          :current="stats.frame === 'month'"
          @click="$emit('navigate', ['month', 0])"
        >
          {{ $t('retour.dashboard.month') }}
        </k-button>
        <k-button
          icon="menu"
          :current="stats.frame === 'week'"
          @click="$emit('navigate', ['week', 0])"
        >
          {{ $t('retour.dashboard.week') }}
        </k-button>
        <k-button
          icon="clock"
          :current="stats.frame === 'day'"
          @click="$emit('navigate', ['day', 0])"
        >
          {{ $t('retour.dashboard.day') }}
        </k-button>
        <k-button
          icon="angle-right"
          :disabled="stats.offset >= 0"
          @click="$emit('navigate', [stats.frame, stats.offset + 1])"
        />
      </k-button-group>
    </header>

    <div v-show="chart" class="k-card k-card-content">
      <div class="ct-timeline" />
    </div>
  </div>
</template>

<script>
import Chartist from "chartist";

export default {
  props: {
    stats: Object
  },
  computed: {
    chart() {
      if (!this.stats.data) {
        return;
      }

      return {
        labels: this.stats.data.labels,
        series: [
          this.stats.data.fails.map((x, i) => x + this.stats.data.redirects[i]),
          this.stats.data.redirects,
        ]
      };
    },
    options() {
      return {
        height: 250,
        showLabel: false,
        low: 0,
        showArea: true,
        showLine: false,
        showPoint: false,
        fullWidth: true,
      };
    }
  },
  watch: {
    chart() {
      this.createChart();
    }
  },
  methods: {
    createChart() {
      new Chartist.Line(".ct-timeline", this.chart, this.options);
    }
  }
}
</script>

<style>
.ct-timeline {
  margin-left: -.75rem;
}

.ct-timeline .ct-label.ct-horizontal.ct-end {
  display: block;
  transform: translateX(-50%);
  text-align: center;
}

.ct-timeline .ct-series-a .ct-area {
  fill: #ccc;
  fill-opacity: .5;
}
.ct-timeline .ct-series-b .ct-area {
  fill: #4271ae;
  fill-opacity: .75;
}
</style>
