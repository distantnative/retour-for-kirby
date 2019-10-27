<template>
  <abbr class="rt-recency" :title="'Last: ' + (tooltip || value || '–')">
    <aside>
      <k-icon type="circle" class="bg" />
      <k-icon type="circle" :style="'--f:' + factor" />
    </aside>
  </abbr>
</template>

<script>
export default {
  props: {
    value: String,
    tooltip: String
  },
  computed: {
    date() {
      return new Date(this.value.replace(/-/g, "/"));
    },
    factor() {
      if (!this.value) {
        return 0;
      }

      const diff   = Math.abs((new Date()).getTime() - this.date.getTime());
      const days   = Math.ceil(diff / (1000 * 3600 * 24));
      const factor = 1 - (days/30);
      return factor > 0 ? factor : 0;
    },
    short() {
      let day   = this.date.getDate().toString().padStart(2, "0");
      let month = (this.date.getMonth() + 1).toString().padStart(2, "0");
      let year  = this.date.getFullYear().toString().substr(-2);
      return `${day}/${month}/${year}`;
    }
  }
}
</script>

<style lang="scss">
.rt-recency {
  position: relative;

  &[title] {
    text-decoration: none;
  }

  .k-icon {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    color: var(--color-focus);
    opacity: var(--f);
  }

  .bg {
    position: absolute;
    color: var(--color-border);
  }
}
</style>
