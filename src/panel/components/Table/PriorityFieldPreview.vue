<template>
  <div class="k-priority-field-preview">
    <k-icon v-if="value" type="bolt" :title="column.label" />
    <p v-else>–</p>
  </div>
</template>

<script>
export default {
  props: {
    value: Boolean,
    column: Object,
  },
};
</script>

<style>
.k-priority-field-preview {
  display: flex;
  justify-content: center;
}
.k-priority-field-preview p {
  color: var(--color-gray-400);
}
.k-priority-field-preview .k-icon {
  transform: scale(0.8);
}
</style>
