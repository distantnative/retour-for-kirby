<template>
  <div class="rt-stats-box rt-timeline" />
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
              if (this.$store.getters["retour/view"] === "year") {
                return index % 4  === 0 ? value : null;
              }

              if (this.$store.getters["retour/view"] === false) {
                return index % (parseInt(this.labels.length/30) + 2)  === 0 ? value : null;
              }

              return index % 3  === 0 ? value : null;
            }.bind(this)
          }
        }],
        ['screen and (min-width: 45em)', {
          axisX: {
            labelInterpolationFnc: function(value, index) {
              if (this.$store.getters["retour/view"] === "year") {
                return index % 2  === 0 ? value : null;
              }

              if (this.$store.getters["retour/view"] === false) {
                return index % (parseInt(this.labels.length/30) + 1)  === 0 ? value : null;
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

<style lang="scss">
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
