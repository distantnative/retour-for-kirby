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

    <div class="k-card k-card-content">
      <pie :height="350" :chart-data="data" :options="options" />
    </div>
  </div>
</template>

<script>

import getGradients from './../../assets/gradients.js';
import Pie  from './Pie.vue';

export default {
  components: {
    pie: Pie
  },
  props: {
    response: Object
  },
  data () {
    return {
      data: null,
      redirects: '–',
      fails: '-'
    }
  },
  computed: {
    options() {
      return {
        legend: false,
        rotation: 1 * Math.PI,
        circumference: Math.PI
      };
    }
  },
  watch: {
    response(response) {
      let gradients  = getGradients(document, 'pie-chart');
      this.redirects = response.redirects.reduce((sum, x) => sum + x);
      this.fails     = response.fails.reduce((sum, x) => sum + x);

      this.data = {
        labels: [this.$t('retour.redirects'), this.$t('retour.fails')],
        datasets: [
          {
            data: [
              this.redirects,
              this.fails
            ],
            backgroundColor: [gradients.blue, gradients.grey],
            hoverBackgroundColor: ['#4271ae', '#ccc'],
            borderWidth: [1, 1]
          }
        ]
      }
    }
  }
}
</script>

<style lang="scss">
  .k-button.retour-redirects {
    pointer-events: none;

    .k-icon {
      color: #4271ae;
    }
  }

  .k-button.retour-fails {
    pointer-events: none;

    .k-icon {
      color: #aaa;
    }
  }
</style>

