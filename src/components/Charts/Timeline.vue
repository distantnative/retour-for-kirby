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

    <div class="rt-stats-box rt-timeline" />
  </div>
</template>

<script>
import Chartist from "chartist";

export default {
  computed: {
    data() {
      return this.$store.state.retour.data.stats.sort((a, b) => parseInt(a.time) - parseInt(b.time));
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
        ['screen and (max-width: 45em)', {
          axisX: {
            labelInterpolationFnc: function(value, index) {
              if (this.view === "year") {
                return index % 4  === 0 ? value : null;
              }

              return index % 3  === 0 ? value : null;
            }.bind(this)
          }
        }],
        ['screen and (min-width: 45em)', {
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

      let chart = new Chartist.Line(".rt-timeline", {
        labels: this.labels,
        series: [
          this.data.map(x => parseInt(x.total)),
          this.data.map(x => parseInt(x.resolved) + parseInt(x.redirected)),
          this.data.map(x => parseInt(x.resolved) + parseInt(x.redirected)),
          this.data.map(x => parseInt(x.redirected)),
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

      chart.on('created', function(ctx) {
        var mask1 = ctx.svg.elem('defs').elem('mask', {
          id: 'mask1'
        });

        mask1.elem('rect', {
          width: '100%',
          height: '100%',
          fill: 'white'
        });

        mask1.append(ctx.svg.querySelector('.ct-series.ct-series-b')).querySelector('.ct-area').attr({
          style: 'fill: black; fill-opacity: 1'
        });

        var mask2 = ctx.svg.elem('defs').elem('mask', {
          id: 'mask2'
        });

        mask2.elem('rect', {
          width: '100%',
          height: '100%',
          fill: 'white'
        });

        mask2.append(ctx.svg.querySelector('.ct-series.ct-series-d')).querySelector('.ct-area').attr({
          style: 'fill: black; fill-opacity: 1'
        });

        ctx.svg.querySelector('.ct-series.ct-series-a').attr({
          mask: 'url(#mask1)'
        });

        ctx.svg.querySelector('.ct-series.ct-series-c').attr({
          mask: 'url(#mask2)'
        });
      });
    }
  }
}
</script>

<style>
.rt-timeline > svg {
  margin-top: .75rem;
  margin-left: -.5rem;
}

.rt-timeline .ct-grid,
.rt-timeline .ct-label {
  color: #74788b;
  stroke: #74788b;
}

.ct-label.ct-horizontal.ct-end {
  transform: translateX(-25%);
  text-align: center;
}
</style>
