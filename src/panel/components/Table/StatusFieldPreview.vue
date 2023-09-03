<template>
  <div :title="`${column.label}: ${value}`" class="k-status-field-preview">
    <k-icon type="circle-filled" :style="'color: ' + color" />
    <kbd>{{ value }}</kbd>
  </div>
</template>

<script>
import color from "../../mixins/color.js";

export default {
  mixins: [color],
  props: {
    value: Number,
    row: Object,
    column: Object,
  },
};
</script>

<style>
.k-status-field-preview {
  display: flex;
  gap: var(--spacing-2);
  padding-inline: var(--spacing-2);
}
.k-status-field-preview kbd {
  font-size: var(--text-xs);
  font-family: var(--font-mono);
}
</style>
