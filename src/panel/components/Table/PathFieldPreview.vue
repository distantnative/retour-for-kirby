<template>
  <p class="k-url-field-preview k-path-field-preview">
    <k-link :to="link" :title="`${column.label}: ${value}`" @click.native.stop>
      {{ value }}
    </k-link>
  </p>
</template>

<script>
export default {
  props: {
    column: {
      type: Object,
      default: () => ({}),
    },
    field: Object,
    value: [String, Object],
  },
  computed: {
    isExternal() {
      return this.value && this.value.startsWith("http");
    },
    link() {
      return this.isExternal
        ? this.value
        : window.panel.$urls.site + "/" + this.value;
    },
  },
};
</script>
