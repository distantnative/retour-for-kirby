<template>
  <div :title="`${column.label}: ${value}`" class="k-table-link-cell">
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
    column: Object,
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

<style>
.k-table-link-cell {
  position: relative;
  padding: 0.5rem;
  padding-inline-start: 2.25rem;
}
.k-table-link-cell .k-icon {
  color: var(--color-border);
  transform: scale(0.8);
}
.k-table-link-cell .k-button {
  position: absolute;
  inset-inline-start: 0.5rem;
}
.k-table-link-cell .k-button:hover .k-icon {
  color: var(--color-gray-600);
}
</style>
