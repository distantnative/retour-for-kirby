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
      <div class="ct-share"></div>
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
        height: 250,
        startAngle: 270,
        total: (this.redirects + this.fails)*2,
        showLabel: false
      };
    }
  },
  watch: {
    response(response) {
      this.redirects = response.redirects.reduce((sum, x) => sum + x);
      this.fails     = response.fails.reduce((sum, x) => sum + x);
      this.data      = { series: [this.redirects, this.fails] };
      this.createChart();
    }
  },
  methods: {
    createChart() {
      new Chartist.Pie('.ct-share', this.data, this.options);
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

  .ct-share {
    height: 150px;
  }

  .ct-share .ct-series-a .ct-slice-pie {
    fill: url(#gradient-redirects) #4271ae;
  }

  .ct-share .ct-series-b .ct-slice-pie {
    fill: url(#gradient-fails) #ccc;
  }

</style>

