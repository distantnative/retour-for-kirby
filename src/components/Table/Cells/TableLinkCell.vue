<template>
  <div
    :title="`${column.label}: ${value}`"
    class="px-2"
  >
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
