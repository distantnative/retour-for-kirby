<template>
  <figure class="rt-chart">
    <div class="chart" />

    <figcaption>
      <div>
        <k-icon type="circle" data-type="redirected" />
        {{ redirected }} {{ $t('retour.stats.redirected') }}
      </div>
      <div>
        <k-icon type="circle" data-type="resolved" />
        {{ resolved }} {{ $t('retour.stats.resolved') }}
      </div>
      <div>
        <k-icon type="circle" data-type="failed" />
        {{ failed }} {{ $t('retour.stats.failed') }}
      </div>
    </figcaption>
  </figure>
</template>

<script>
import Chartist from "chartist";

export default {
  data() {
    return {
      chart: null
    }
  },
  computed: {
    data() {
      return this.$store.state.retour.data.stats;
    },
    redirected() {
      return this.data.reduce((i, x) => i += x.redirected, 0);
    },
    resolved() {
      return this.data.reduce((i, x) => i += x.resolved, 0);
    },
    failed() {
      return this.data.reduce((i, x) => i += x.failed, 0);
    },
    total() {
      return this.redirected + this.resolved + this.failed
    }
  },
  watch: {
    data: {
      handler() {
        this.update();
      },
      deep: true
    }
  },
  mounted() {
    this.chart = new Chartist.Pie(".rt-chart .chart", {}, {
      height: 300,
      startAngle: 270,
      showLabel: false
    });
    this.update();
  },
  methods: {
    update() {
      this.chart.update({
        series: [
          this.redirected,
          this.resolved,
          this.failed,
          this.total > 0 ? 0 : 1
        ]
      }, {
        total: (this.total > 0 ? this.total : 1) * 2,
      },
      true)
    }
  }
}
</script>

<style lang="scss">
.rt-chart {
  margin-top: -.5rem;
  padding: 1rem 1rem 1.5rem;
}
.rt-chart figcaption {
  padding-left: .75rem;
  padding-right: .75rem;
  margin-top: -124px;
}
.rt-chart figcaption > div {
  display: flex;

  + div {
    margin-top: .25rem;
  }
}
.rt-chart figcaption .k-icon {
  padding-right: .75rem;
}

.rt-chart .ct-series-c .ct-slice-pie,
.rt-chart .k-icon[data-type="failed"] {
  color: var(--color-negative);
  fill: var(--color-negative);
  fill-opacity: .85;
}
.rt-chart .ct-series-a .ct-slice-pie,
.rt-chart .k-icon[data-type="redirected"] {
  color: var(--color-focus);
  fill: var(--color-focus);
  fill-opacity: .85;
}
.rt-chart .ct-series-b .ct-slice-pie,
.rt-chart .k-icon[data-type="resolved"] {
  color: var(--color-border);
  fill: var(--color-border);
  fill-opacity: .85;
}

.rt-chart .ct-series {
  stroke: #2b2b2b;
  stroke-width: .2rem;
}

.rt-chart .ct-series-d .ct-slice-pie {
  fill: #3d3d3d;
}
</style>
