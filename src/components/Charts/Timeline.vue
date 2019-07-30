<template>
  <div>
    <header class="k-field-header">
      <label class="k-field-label" />

      <k-button-group>
        <k-button
          icon="angle-left"
          @click="$store.dispatch('retour/offset', -1)"
        />
        <k-button
          v-for="by in ['year', 'month', 'week', 'day']"
          :key="by"
          :current="view === by"
          @click="$store.dispatch('retour/stats', by)"
        >
          {{ $t('rt.stats.' + by) }}
        </k-button>
        <k-button
          icon="angle-right"
          :disabled="$store.state.retour.view.offset >= 0"
          @click="$store.dispatch('retour/offset', 1)"
        />
      </k-button-group>
    </header>

    <div class="k-card k-card-content rt-timeline" />
  </div>
</template>

<script>
import Chartist from "chartist";

export default {
  computed: {
    data() {
      return this.$store.state.retour.data.stats.sort((a, b) => {
        return parseInt(a.time) - parseInt(b.time)
      });
;
    },
    labels() {
      return this.data.map(x => x.label);
    },
    view() {
      return this.$store.state.retour.view.stats;
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

      const responsive = [
        ['screen', {
          axisX: {
            labelInterpolationFnc: function(value, index) {
              if (this.view === "year") {
                return index % 2  === 0 ? value : null;
              }

              return value;
            }.bind(this)
          }
        }]
      ];

      new Chartist.Line(".rt-timeline", {
        labels: this.labels,
        series: [
          this.data.map(x => parseInt(x.total)),
          this.data.map(x => parseInt(x.resolved) + parseInt(x.redirected)),
          this.data.map(x => parseInt(x.redirected)),
        ]
      }, {
        height: 240,
        showLabel: false,
        low: 0,
        showArea: true,
        showLine: false,
        showPoint: false,
        fullWidth: true,
        axisY: {
          onlyInteger: true
        }
      }, responsive);
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
  transform: translateX(-25%);
  text-align: center;
}

.rt-timeline .ct-series-a .ct-area {
  fill: #c82828;
  fill-opacity: .75;
}
.rt-timeline .ct-series-b .ct-area {
  fill: #ddd;
  fill-opacity: 1;
}
.rt-timeline .ct-series-c .ct-area {
  fill: #4271ae;
  fill-opacity: 1;
}

</style>
