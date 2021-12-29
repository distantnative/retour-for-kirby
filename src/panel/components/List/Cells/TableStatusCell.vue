<template>
  <div :title="`${column.label}: ${value || '-'}`" class="k-table-status-cell">
    <k-icon type="circle" :style="'color: ' + color" />
    <code v-if="value">{{ value }}</code>
    <span v-else>&nbsp;–</span>
  </div>
</template>

<script>
import color from "../../../mixins/color.js";

export default {
  mixins: [color],
  props: {
    value: String,
    row: Object,
    column: Object,
  },
};
</script>

<style>
.k-table-status-cell {
  display: flex;
  padding-left: 0.5rem;
  padding-right: 0.5rem;
}
.k-table-status-cell .k-icon {
  margin-right: 0.5rem;
}
.k-table-status-cell code {
  background: rgba(0, 0, 0, 0.1);
  color: var(--color-text);
  border-radius: 3px;
  box-decoration-break: clone;
  font-family: var(--font-mono);
  font-size: var(--text-xs);
  padding: 0.05em 0.5em;
}
</style>
