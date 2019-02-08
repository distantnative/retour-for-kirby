<template>
  <div>
    <header class="k-field-header">
      <k-button-group>
        <k-button icon="circle" class="retour-redirects">
          {{ redirects }} {{ $t('retour.redirects') }}
        </k-button>
        <k-button icon="circle" class="retour-fails">
          {{ fails }} {{ $t('retour.fails') }}
        </k-button>
      </k-button-group>
    </header>

    <div v-show="chart" class="k-card k-card-content">
      <div class="ct-share" />
    </div>
  </div>
</template>

<script>
import Chartist from "chartist";

export default {
  props: {
    data: Object
  },
  data () {
    return {
      chart: null,
      redirects: "–",
      fails: "-"
    }
  },
  computed: {
    options() {
      return {
        height: 250,
        startAngle: 270,
        total: (this.redirects + this.fails)*2,
        showLabel: false
      };
    }
  },
  watch: {
    data(data) {
      this.redirects = data.redirects.reduce((a, b) => a += b, 0);
      this.fails     = data.fails.reduce((a, b) => a += b, 0);
      this.chart     = { series: [this.redirects, this.fails] };
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
  height: 150px;
}
</style>

