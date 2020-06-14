<template>
  <span
    :title="`${column.label}: ${value || '-'}`"
    class="retour-table-status-preview"
  >
    <k-icon
      type="circle"
      :color="color"
      class="mr-2"
    />
    <code v-if="value">{{ value }}</code>
    <span v-else class="pl-1">–</span>
  </span>
</template>

<script>
import color from "../mixins/color.js";

export default {
  mixins: [color],
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

<style lang="scss">
.retour-table-status-preview {
  display: flex;

  code {
    background: rgba(0, 0, 0, 0.1);
    color: var(--color-text);
    border-radius: 3px;
    box-decoration-break: clone;
    font-family: var(--font-family-mono);
    font-size: var(--font-size-tiny);
    padding: 0.05em 0.5em;
  }
}
</style>
