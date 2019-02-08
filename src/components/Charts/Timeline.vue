<template>
  <div>
    <header class="k-field-header">
      <label class="k-field-label">
        {{ headline }}
      </label>

      <k-button-group>
        <k-button icon="angle-left" @click="$emit('prev')" />
        <k-button
          icon="calendar"
          :current="view === 'month'"
          @click="$emit('show', 'month')"
        >
          {{ $t('retour.dashboard.timeline.month') }}
        </k-button>
        <k-button
          icon="menu"
          :current="view === 'week'"
          @click="$emit('show', 'week')"
        >
          {{ $t('retour.dashboard.timeline.week') }}
        </k-button>
        <k-button
          icon="clock"
          :current="view === 'day'"
          @click="$emit('show', 'day')"
        >
          {{ $t('retour.dashboard.timeline.day') }}
        </k-button>
        <k-button
          icon="angle-right"
          :disabled="offset >= 0"
          @click="$emit('next')"
        />
      </k-button-group>
    </header>

    <div class="k-card k-card-content">
      <div class="ct-timeline" />
    </div>

    <gradients />
  </div>
</template>

<script>

import Chartist from "chartist";
import Gradients from "./Gradients.vue";

export default {
  components: {
    gradients: Gradients
  },
  props: {
    response: Object,
    view: String,
    offset: Number
  },
  data () {
    return {
      headline: "...",
      data: null
    }
  },
  computed: {
    options() {
      return {
        height: 250,
        showLabel: false,
        low: 0,
        showArea: true,
        showLine: false,
        showPoint: false,
        fullWidth: true,
      };
    }
  },
  watch: {
    response(response) {
      this.headline = response.headline;
      this.data = {
        labels: response.labels,
        series: [
          response.fails,
          response.redirects,
        ]
      };
      this.createChart();
    }
  },
  methods: {
    createChart() {
      new Chartist.Line(".ct-timeline", this.data, this.options);
    }
  }
}
</script>

<style lang="scss">

.ct-timeline {
  margin-left: -.75rem;
}

.ct-timeline .ct-label.ct-horizontal.ct-end {
  display: block;
  transform: translateX(-50%);
  text-align: center;
}

.ct-timeline .ct-series-a .ct-area {
  fill: url(#gradient-fails) #ccc;
  fill-opacity: .65;
}

.ct-timeline .ct-series-b .ct-area {
  fill: url(#gradient-redirects) #4271ae;
  fill-opacity: .75;
}
</style>
