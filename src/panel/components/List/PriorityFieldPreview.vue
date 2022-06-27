<template>
  <div class="k-priority-field-preview">
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
.k-priority-field-preview {
  display: flex;
  justify-content: center;
}
.k-priority-field-preview p {
  color: var(--color-border);
}
.k-priority-field-preview .k-icon {
  transform: scale(0.8);
}
</style>
