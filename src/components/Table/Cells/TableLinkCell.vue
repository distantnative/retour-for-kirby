<template>
  <div
    :title="`${column.label}: ${value}`"
    class="k-table-link-cell"
  >
    <k-button
      v-if="value && value != '-'"
      :link="link"
      icon="url"
      target="_blank"
      @click.native.stop
    />
    {{ value }}
  </div>
</template>

<script>
export default {
  props: {
    value: String,
    column: Object
  },
  computed: {
    isExternal() {
      return this.value && this.value.startsWith('http');
    },
    link() {
      return this.isExternal ? this.value : window.panel.site + '/' + this.value
    }
  }
}
</script>

<style lang="scss">
.k-table-link-cell {
  padding-left: .5rem;
  padding-right: .5rem;

  .k-button {
    margin-right: .5rem;
  }

  .k-icon {
    color: var(--color-border);
    transform: scale(.8);
  }
}
</style>
