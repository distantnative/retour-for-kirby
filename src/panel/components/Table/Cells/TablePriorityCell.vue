<template>
  <div v-if="value" class="k-table-priority-cell">
    <k-icon
      type="bolt"
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

<style>
.k-table-priority-cell {
  display: flex;
  justify-content: center;
}
.k-table-priority-cell .k-icon {
  transform: scale(.8);
}
</style>
