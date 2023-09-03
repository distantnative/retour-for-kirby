<template>
  <div class="k-retour-tabs" :data-end="tabs.length < 2">
    <k-tabs :tab="tab" :tabs="tabs" />
    <slot name="buttons" />
  </div>
</template>

<script>
export default {
  props: {
    tab: String,
    tabs: Array,
  },
};
</script>

<style>
.k-retour-tabs {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--spacing-6);
}
.k-retour-tabs[data-end="true"] {
  justify-content: end;
}
.k-retour-tabs .k-tabs {
  margin-bottom: 0;
}
.k-retour-tabs .k-tab-button:nth-child(1) .k-tabs-badge {
  --theme-color-back: var(--color-blue-300);
}
.k-retour-tabs .k-tab-button:nth-child(2) .k-tabs-badge {
  --theme-color-back: var(--color-red-300);
}
</style>
