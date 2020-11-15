<template>
  <figure class="rt-timeline">
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
    mode() {
      return this.$store.getters["retour/mode"];
    },
    unit() {
      if (this.mode === "day") {
        return "hour";
      }

      return "day";
    },
    diff () {
      return this.max.diff(this.min, this.unit);
    },
    ticks() {
      let ticks = Array.from({ length: this.diff + 1 }).map((e, index) => {
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
      if (this.mode === "day") {
        return "HH"
      }

      if (this.mode === "week") {
        return "ddd"
      }

      if (this.mode === "month") {
        return "D";
      }

      if (this.mode === "year") {
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
  mounted() {
    this.createChart();
  },
  methods: {
    createChart() {
      let chart = new Chartist.Line(
        ".rt-timeline .chart",
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
          height: 275,
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
.rt-timeline {
  padding: .75rem 0;
}
.rt-timeline .chart > svg {
  margin-left: -1rem;
}
.rt-timeline .ct-grid,
.rt-timeline .ct-label {
  color: #777;
  stroke: #777;
  stroke-dasharray: 2px;
}
.rt-timeline .ct-label.ct-vertical.ct-start {
  font-size: .8rem;
  padding-left: 1rem;
}
.rt-timeline .ct-label.ct-horizontal.ct-end {
  display: block;
  transform: translateX(-50%);
  text-align: center !important;
  text-anchor: middle !important;
  font-size: .8rem;
}

.rt-timeline .ct-series-a .ct-area {
  fill: var(--color-negative);
  fill-opacity: .85;
}
.rt-timeline .ct-series-e .ct-area {
  fill: var(--color-focus);
  fill-opacity: .85;
}
.rt-timeline .ct-series-c .ct-area {
  fill: var(--color-border);
  fill-opacity: .85;
}
</style>
