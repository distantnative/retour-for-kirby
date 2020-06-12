<template>
  <figure class="retour-timeline p-3">
    <div class="chart" />
  </figure>
</template>

<script>
import Chartist from "chartist";

export default {
  computed: {
    data() {
      return this.$store.state.retour.data.stats;
    },
    failed() {
      return this.data.map(d => ({
        x: this.$library.dayjs(d.date),
        y: d.failed + d.resolved + d.redirected
      }));
    },
    resolved() {
      return this.data.map(d => ({
        x: this.$library.dayjs(d.date),
        y: d.resolved + d.redirected
      }));
    },
    redirected() {
      return this.data.map(d => ({
        x: this.$library.dayjs(d.date),
        y: d.redirected
      }));
    },
    min() {
      return this.$library.dayjs(this.data[0].date);
    },
    max() {
      return this.$library.dayjs(this.data[this.data.length - 1].date);
    },
    selection() {
      return this.$store.getters["retour/selection"];
    },
    unit() {
      if (this.selection === "day") {
        return "hour";
      }

      return "day";
    },
    diff () {
      return this.max.diff(this.min, this.unit);
    },
    ticks() {
      let ticks = Array.from({ length: this.diff + 1 })
                  .map((e, index) => {
                    return this.$library.dayjs(this.min).add(index, this.unit);
                  });

      // Only show month borders
      if (this.diff > 62) {
        return ticks.filter(x => x.get("date") === 1);
      }

      // Always show 5-6 ticks only
      if (this.diff > 31) {
        return ticks.filter((x, i) => i%(parseInt(this.diff/5)) === 0);
      }

      return ticks;
    },
    format() {
      if (this.selection === "day") {
        return "HH"
      }

      if (this.selection === "week") {
        return "ddd"
      }

      if (this.selection === "month") {
        return "D";
      }

      if (this.selection === "year") {
        return "MMM";
      }

      if (this.diff > 62) {
        return "MMM YYYY";
      }

      return "D MMM";
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
      let chart = new Chartist.Line(
        ".retour-timeline .chart",
        {
          labels: this.labels,
          series: [
            this.failed,
            this.resolved,
            this.resolved,
            this.redirected,
            this.redirected
          ]
        },
        {
          height: 240,
          showLabel: false,
          low: 0,
          showArea: true,
          showLine: false,
          showPoint: false,
          fullWidth: true,
          lineSmooth: Chartist.Interpolation.simple({
            divisor: 5
          }),
          axisY: {
            onlyInteger: true
          },
          axisX: {
            type: Chartist.FixedScaleAxis,
            ticks: this.ticks,
            labelInterpolationFnc: function(value) {
              return value.format(this.format);
            }.bind(this)
          }
        },
        [
          [
            "screen and (max-width: 45em)",
            {
              axisX: {
                labelInterpolationFnc: function(value, index) {
                  return index % 2 === 0 ? value.format(this.format) : null;
                }.bind(this)
              }
            }
          ]
        ]
      );

      chart.on("created", function(ctx) {
        var mask1 = ctx.svg.elem("defs").elem("mask", {
          id: "mask1"
        });

        mask1.elem("rect", {
          width: "100%",
          height: "100%",
          fill: "white"
        });

        mask1
          .append(ctx.svg.querySelector(".ct-series.ct-series-b"))
          .querySelector(".ct-area")
          .attr({
            style: "fill: black; fill-opacity: 1"
          });

        var mask2 = ctx.svg.elem("defs").elem("mask", {
          id: "mask2"
        });

        mask2.elem("rect", {
          width: "100%",
          height: "100%",
          fill: "white"
        });

        mask2
          .append(ctx.svg.querySelector(".ct-series.ct-series-d"))
          .querySelector(".ct-area")
          .attr({
            style: "fill: black; fill-opacity: 1"
          });

        ctx.svg.querySelector(".ct-series.ct-series-a").attr({
          mask: "url(#mask1)"
        });

        ctx.svg.querySelector(".ct-series.ct-series-c").attr({
          mask: "url(#mask2)"
        });
      });
    }
  }
}
</script>

<style lang="scss">
.retour-timeline {
  background: rgba(0, 0, 0, .2);
}
.retour-timeline .chart > svg {
  margin-top: .5rem;
  margin-left: -.75rem;
}

.retour-timeline .ct-grid,
.retour-timeline .ct-label {
  color: #555;
  stroke: #555;
}
.retour-timeline .ct-label.ct-vertical.ct-start {
  font-size: .8rem;
  padding-left: 1rem;
}
.retour-timeline .ct-label.ct-horizontal.ct-end {
  display: block;
  transform: translateX(-50%);
  text-align: center !important;
  text-anchor: middle !important;
  font-size: .8rem;
}

.retour-timeline .ct-series-a .ct-area {
  fill: var(--color-negative);
  fill-opacity: .85;
}
.retour-timeline .ct-series-e .ct-area {
  fill: var(--color-focus);
  fill-opacity: .85;
}
.retour-timeline .ct-series-c .ct-area {
  fill: var(--color-border);
  fill-opacity: .85;
}
</style>
