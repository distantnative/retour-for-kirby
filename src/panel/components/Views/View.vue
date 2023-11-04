<template>
  <k-inside class="k-retour-view">
    <k-header>
      {{  $t('view.retour') }}

      <template v-if="stats" #buttons>
        <k-retour-timespan :timespan="timespan" />
      </template>
    </k-header>

    <k-retour-stats v-if="stats" :data="stats" :timespan="timespan" />

    <k-retour-tabs
      :tab="tab"
      :tabs="tabs"
    >
      <slot name="buttons" />
    </k-retour-tabs>

    <slot />
  </k-inside>
</template>

<script>
export const props = {
  props: {
    data: [Object, Array],
    stats: [Boolean, Array],
    tab: String,
    tabs: Array,
    timespan: Object,
  }
}

export default {
  mixins: [props],
};
</script>

<style>
.k-retour-view .k-header {
  margin-bottom: 0;
}
</style>
