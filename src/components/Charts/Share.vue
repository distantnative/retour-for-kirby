<template>
  <div>
    <header class="k-field-header">
      <label class="k-field-label">
        {{ data ? data.headline : '...' }}
      </label>

      <k-button-group>
        <k-button icon="smile" class="hide" />
      </k-button-group>
    </header>

    <div v-show="chart" class="k-card k-card-content">
      <div class="ct-share" />
    </div>

    <footer class="k-field-footer">
      <k-button-group>
        <k-button icon="circle" class="retour-redirects">
          {{ redirects }} {{ $t('retour.redirects') }}
        </k-button>
        <k-button icon="circle" class="retour-fails">
          {{ fails }} {{ $t('retour.fails') }}
        </k-button>
      </k-button-group>
    </footer>
  </div>
</template>

<script>
import Chartist from "chartist";

export default {
  props: {
    data: Object
  },
  computed: {
    chart() {
      if ((this.redirects + this.fails) === 0) {
        return {};
      }

      return { series: [this.redirects, this.fails] };
    },
    fails() {
      if (!this.data) {
        return '-';
      }

      return this.data.fails.reduce((a, b) => a += b, 0);
    },
    options() {
      return {
        height: 300,
        startAngle: 270,
        total: (this.redirects + this.fails)*2,
        showLabel: false
      };
    },
    redirects() {
      if (!this.data) {
        return '-';
      }

      return this.data.redirects.reduce((a, b) => a += b, 0);
    }
  },
  watch: {
    data() {
      this.createChart();
    }
  },
  methods: {
    createChart() {
      new Chartist.Pie(".ct-share", this.chart, this.options);
    }
  }
}
</script>

<style>
.k-button.retour-redirects,
.k-button.retour-fails {
  pointer-events: none;
}

.k-button.retour-redirects .k-icon,
.ct-share .ct-series-a .ct-slice-pie {
  color: #4271ae;
  fill:#4271ae;
}

.k-button.retour-fails .k-icon,
.ct-share .ct-series-b .ct-slice-pie {
  color: #aaa;
  fill: #ccc;
}

.ct-share {
  height: 180px;
}
</style>

