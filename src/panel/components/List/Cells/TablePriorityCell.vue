<template>
  <div class="k-table-priority-cell">
    <k-icon v-if="value" type="bolt" :title="column.label" />
    <!-- eslint-disable vue/singleline-html-element-content-newline -->
    <p v-else>–</p>
  </div>
</template>

<script>
export default {
  props: {
    value: String,
    column: Object,
  },
};
</script>

<style>
.k-table-priority-cell {
  display: flex;
  justify-content: center;
}
.k-table-priority-cell p {
  color: var(--color-border);
}
.k-table-priority-cell .k-icon {
  transform: scale(0.8);
}
</style>
