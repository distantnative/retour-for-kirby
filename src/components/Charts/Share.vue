<template>
  <div>
    <header class="k-field-header">
      <label class="k-field-label">
        {{ $store.state.retour.view.title }}
      </label>

      <k-button-group>
        <k-button
          icon="refresh"
          :disabled="$store.state.isLoading"
          @click="$store.dispatch('retour/load')"
        />
      </k-button-group>
    </header>

    <div class="k-card k-card-content">
      <div class="rt-share" />

      <footer class="k-field-footer">
        <k-button icon="circle" class="rt-lb-redirected">
          {{ redirects }} {{ $t('rt.redirected') }}
        </k-button><br>
        <k-button icon="circle" class="rt-lb-resolved">
          {{ resolved }} {{ $t('rt.resolved') }}
        </k-button><br>
        <k-button icon="circle" class="rt-lb-failed">
          {{ fails }} {{ $t('rt.failed') }}
        </k-button>
      </footer>
    </div>


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
        series: [this.redirects, this.resolved, this.fails, this.total > 0 ? 0 : 1]
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
  margin-top: -1.65rem;
  padding: 0 .5rem 1rem;
  pointer-events: none;
  color: #000;
}

.ct-series {
  stroke: #fff;
  stroke-width: 2px;
}

.ct-series-d .ct-slice-pie {
  fill: #f5f5f5;
}
</style>

