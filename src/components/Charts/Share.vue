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

    <div class="k-card k-card-content rt-share" />

    <footer class="k-field-footer">
      <k-button-group>
        <k-button icon="circle" class="rt-lb-redirects">
          {{ redirects }} {{ $t('rt.redirects') }}
        </k-button>
        <k-button icon="circle" class="rt-lb-fails">
          {{ fails }} {{ $t('rt.fails') }}
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
    fails() {
      if (!this.data) {
        return "-";
      }

      return this.data.failed.reduce((a, b) => a += b || 0, 0);
    },
    redirects() {
      if (!this.data) {
        return "-";
      }

      return this.data.redirected.reduce((a, b) => a += b || 0, 0);
    },
    total() {
      return this.redirects + this.fails;
    },
  },
  watch: {
    data() {
      this.createChart();
    }
  },
  created() {
    this.$events.$on("retour-go", (part) => {
      if (part === "dashboard") {
        this.createChart();
      }
    });
  },
  methods: {
    createChart() {
      new Chartist.Pie(".rt-share", {
        series: [this.redirects, this.fails, this.total > 0 ? 0 : 1]
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
.rt-lb-redirects,
.rt-lb-fails {
  pointer-events: none;
}

.rt-share { height: 200px }

.rt-share .ct-series {
    stroke: #fff;
    stroke-width: 3px;
  }

.rt-lb-redirects .k-icon,
.rt-share .ct-series-a .ct-slice-pie {
  color: #4271ae;
  fill:#4271ae;
}

.rt-lb-fails .k-icon,
.rt-share .ct-series-b .ct-slice-pie {
  color: #aaa;
  fill: #ccc;
}

.rt-share .ct-series-c .ct-slice-pie {
  fill: #f3f3f3;
}
</style>

