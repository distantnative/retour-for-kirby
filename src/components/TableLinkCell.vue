<template>
  <span :title="`${column.label}: ${value}`">
    <k-button
      v-if="value && value != '-'"
      :icon="{
        type: 'url',
        color: 'gray-light',
        size: 'small'
      }"
      :link="link"
      target="_blank"
      class="mr-1"
      @click.native.stop
    />
    {{ value }}
  </span>
</template>

<script>
export default {
  props: {
    value: String,
    column: {
      type: Object,
      default() {
        return {};
      }
    }
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
