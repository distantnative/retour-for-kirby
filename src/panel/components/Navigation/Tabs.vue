<template>
  <div class="k-retour-tabs">
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
.k-retour-tabs .k-tabs {
  margin-bottom: 0;
}
.k-retour-tabs .k-tab-button[title="Routes"] .k-tabs-badge {
  background: var(--color-blue-300);
}
.k-retour-tabs .k-tab-button[title="Failures"] .k-tabs-badge {
  background: var(--color-red-300);
}
</style>
