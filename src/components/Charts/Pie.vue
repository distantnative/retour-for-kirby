<template>
  <div class="rt-stats-box">
    <div class="rt-share" />

    <footer class="k-field-footer">
      <k-button icon="circle" class="rt-lb-redirected">
        {{ redirected }} {{ $t('retour.redirected') }}
      </k-button><br>
      <k-button icon="circle" class="rt-lb-resolved">
        {{ resolved }} {{ $t('retour.resolved') }}
      </k-button><br>
      <k-button icon="circle" class="rt-lb-failed">
        {{ failed }} {{ $t('retour.failed') }}
      </k-button>
    </footer>
  </div>
</template>

<script>
import Chartist from "chartist";

export default {
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
        this.createChart();
      },
      deep: true
    }
  },
  methods: {
    createChart() {
      new Chartist.Pie(".rt-share", {
        series: [
          this.redirected,
          this.resolved,
          this.failed,
          this.total > 0 ? 0 : 1
        ]
      }, {
        height: 300,
        startAngle: 270,
        total: (this.total > 0 ? this.total : 1) * 2,
        showLabel: false
      });
    }
  }
}
</script>

<style>
.rt-share {
  height: 200px
}

.rt-share + .k-field-footer {
  margin-top: -1.35rem;
  padding: 0 .5rem 1.45rem;
  pointer-events: none;
  user-select: none;
}

.ct-series {
  stroke: #3a3c45;
  stroke-width: .2rem;
}

.ct-series-d .ct-slice-pie {
  fill: #2d2f36;
}
</style>
