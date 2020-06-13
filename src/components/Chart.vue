<template>
  <figure class="retour-chart pt-4 pb-6 px-4">
    <div class="chart" />

    <figcaption>
      <div>
        <k-icon type="circle" color="focus" />
        {{ redirected }} {{ $t('retour.stats.redirected') }}
      </div>
      <div>
        <k-icon type="circle" color="border" />
        {{ resolved }} {{ $t('retour.stats.resolved') }}
      </div>
      <div>
        <k-icon type="circle" color="negative" />
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
    this.chart = new Chartist.Pie(".retour-chart .chart", {}, {
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
.retour-chart figcaption {
  margin-top: -124px;
}
.retour-chart figcaption > div {
  display: flex;

  + div {
    margin-top: .25rem;
  }
}
.retour-chart figcaption .k-icon {
  padding-right: .75rem;
}

.retour-chart .ct-series-c .ct-slice-pie {
  fill: var(--color-negative);
  fill-opacity: .85;
}
.retour-chart .ct-series-a .ct-slice-pie {
  fill: var(--color-focus);
  fill-opacity: .85;
}
.retour-chart .ct-series-b .ct-slice-pie {
  fill: var(--color-border);
  fill-opacity: .85;
}

.retour-chart .ct-series {
  stroke: #3d3d3d;
  stroke-width: .2rem;
}

.retour-chart .ct-series-d .ct-slice-pie {
  fill: #333;
}
</style>
