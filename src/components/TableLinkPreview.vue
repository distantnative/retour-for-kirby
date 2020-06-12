<template>
  <span class="retour-table-link-preview">
    <k-button
      v-if="value && value != '-'"
      :link="link"
      icon="url"
      target="_blank"
      @click.native.stop
    />
    {{ value }}
  </span>
</template>

<script>
export default {
  props: {
    value: String
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
.retour-table-link-preview .k-icon-url {
  margin-right: 0.2rem;
  transform: scale(0.8);
  color: var(--color-border);

  &:hover {
    color: var(--color-text-light);
  }
}
</style>
