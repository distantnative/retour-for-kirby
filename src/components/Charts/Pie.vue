<template>
  <div class="rt-stats-box">
    <div class="rt-share" />

    <footer class="k-field-footer">
      <k-button icon="circle" class="rt-lb-redirected">
        {{ redirects }} {{ $t('retour.redirected') }}
      </k-button><br>
      <k-button icon="circle" class="rt-lb-resolved">
        {{ resolved }} {{ $t('retour.resolved') }}
      </k-button><br>
      <k-button icon="circle" class="rt-lb-failed">
        {{ fails }} {{ $t('retour.failed') }}
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
    fails() {
      return this.total - this.redirects - this.resolved;
    },
    redirects() {
      return this.data.reduce((i, x) => i += parseInt(x.redirected), 0);
    },
    resolved() {
      return this.data.reduce((i, x) => i += parseInt(x.resolved), 0);
    },
    total() {
      return this.data.reduce((i, x) => i += parseInt(x.total), 0);
    },
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
          this.redirects,
          this.resolved,
          this.fails,
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
  padding: 0 .5rem 1rem;
  pointer-events: none;
  user-select: none;
}

.ct-series {
  stroke: #3a3c45;
  stroke-width: 3px;
}

.ct-series-d .ct-slice-pie {
  fill: #2d2f36;
}
</style>
