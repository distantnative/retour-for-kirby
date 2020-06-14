<template>
  <div v-if="value" class="flex justify-center">
    <k-icon
      type="bolt"
      size="small"
      :title="column.label"
    />
  </div>
</template>

<script>
export default {
  props: {
    value: String,
    column: Object
  }
}
</script>
