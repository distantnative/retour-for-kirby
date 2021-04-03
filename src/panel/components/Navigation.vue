<template>
  <header class="k-header">
    <k-bar :data-loading="$store.state.isLoading" class="k-header-buttons">
      <template #left>
        <dates />
      </template>
      <template #right>
        <prev-next />
      </template>
    </k-bar>

    <k-tabs :tab="tab" :tabs="tabs" />
  </header>
</template>

<script>
import PrevNext from './Interaction/PrevNext.vue';
import Dates from "./Interaction/Dates.vue";

export default {
  components: {
    Dates,
    PrevNext
  },
  props: {
    tab: String,
    tabs: Array
  }
}
</script>

<style>
.retour .k-tab-button[href="#system"] .k-tabs-badge {
  color: var(--color-positive);
}
.retour .k-tab-button[href="#fails"] .k-tabs-badge {
  color: var(--color-negative);
}
.retour .k-tab-button[href="#redirects"] .k-tabs-badge {
  color: var(--color-focus);
}
</style>