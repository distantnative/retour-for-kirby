<template>
  <div>
    <header class="k-field-header">
      <label class="k-field-label">
        {{ $store.state.retour.view.title }}
      </label>

      <k-button-group>
        <k-button icon="blank" />
      </k-button-group>
    </header>

    <div class="k-card k-card-content rt-share" />

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
.rt-lb-redirected,
.rt-lb-failed,
.rt-lb-resolved {
  pointer-events: none;
}

.rt-share { height: 200px }

.rt-share .ct-series {
    stroke: #fff;
    stroke-width: 2px;
  }

.rt-lb-redirected .k-icon,
.rt-share .ct-series-a .ct-slice-pie {
  color: #4271ae;
  fill:#4271ae;
}

.rt-lb-resolved .k-icon,
.rt-share .ct-series-b .ct-slice-pie {
  color: #aaa;
  fill: #ccc;
}

.rt-lb-failed .k-icon,
.rt-share .ct-series-c .ct-slice-pie {
  color: #c82828;
  fill: #c82828;
}

.rt-share .ct-series-c .ct-slice-pie {
  fill-opacity: .75;
}

.rt-share .ct-series-d .ct-slice-pie {
  fill: #f4f4f4;
}

.rt-share + .k-field-footer {
  padding: .5rem;
}

</style>

