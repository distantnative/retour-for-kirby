<template>
  <figure class="chart-pie">
    <div class="graph" :style="'--gradient: ' + gradient" />

    <figcaption>
      <template v-for="segment in data">
        <k-icon
          :key="segment.label + '-icon'"
          :style="'--color:' + segment.color"
          type="circle"
        />
        <div :key="segment.label + '-no'">
          {{ new Intl.NumberFormat().format(segment.data) }}
        </div>
        <div :key="segment.label + '-label'">
          {{ segment.label }}
        </div>
      </template>
    </figcaption>
  </figure>
</template>

<script>
export default {
  props: {
    data: Array
  },
  computed: {
    gradient() {
      let gradient = "";
      let size     = 0;
      const deg    = this.total/180;

      for (let i = 0; i < this.data.length; i++) {
        gradient += `${this.data[i].color} ${size}deg,`;
        size     += this.data[i].data/deg;
        gradient += `${this.data[i].color} ${size}deg,`;
      }

      gradient += "transparent 180deg";
      return gradient;
    },
    total() {
      return this.data.reduce((i, x) => i += x.data, 0);
    }
  }
};
</script>

<style>
.chart-pie {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: .5rem;
}
.chart-pie > .graph {
  width: 100%;
  padding-bottom: 100%;
  opacity: .85;
  background: #3d3d3d;
  background-image: conic-gradient(from -90deg, var(--gradient));
  border-radius: 50%;
  clip-path: polygon(0% 0%, 0% 50%, 100% 50%, 100% 0%);
  margin-bottom: calc(-50% + 1.5rem);
}
.chart-pie figcaption {
  display: grid;
  grid-template-columns: auto auto auto;
  gap: .5rem;
}
.chart-pie figcaption :nth-child(3n+2) {
  text-align: right;
}
.chart-pie figcaption .k-icon {
  color: var(--color);
}
</style>
