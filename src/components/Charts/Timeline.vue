<template>
  <div>
    <header class="k-field-header">
      <label class="k-field-label">{{ headline }}</label>

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
      <lines
        :height="101"
        :chart-data="data"
        :options="options"
      />
    </div>

  </div>
</template>

<script>

import getGradients from './../../assets/gradients.js';
import Line from './Line.vue';

export default {
  components: {
    lines: Line
  },
  props: {
    response: Object,
    view: String,
    offset: Number
  },
  data () {
    return {
      headline: '...',
      data: null
    }
  },
  computed: {
    options() {
      return {
        legend: false,
        tooltips: false,
        scales: {
          yAxes: [{
            stacked: true,
            display: true,
            ticks: {
              min: 0,
              suggestedMax: 5
            }
          }]
        }
      };
    }
  },
  watch: {
    response(response) {
      let gradients = getGradients(document, 'line-chart');

      this.headline = response.headline;
      this.data = {
        labels: response.labels,
        datasets: [
          {
            backgroundColor: gradients.blue,
            borderColor: 'rgba(66, 113, 174, .75)',
            borderWidth: 1,
            pointRadius: 0,
            data: response.redirects,
          },
          {
            backgroundColor: gradients.grey,
            borderColor: '#ccc',
            borderWidth: 1,
            pointRadius: 0,
            data: response.fails,
          }
        ]
      }
    }
  }
}
</script>

