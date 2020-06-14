<template>
  <k-icon
    v-if="value"
    type="bolt"
    size="small"
    :title="column.label"
  />
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
  }
}
</script>
