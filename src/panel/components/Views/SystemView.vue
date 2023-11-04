<template>
  <k-inside class="k-retour-view k-retour-system-view">
    <k-header>
      {{  $t('view.retour') }}

      <template v-if="stats" #buttons>
        <k-retour-timespan :timespan="timespan" />
      </template>
    </k-header>

    <k-retour-stats v-if="stats" :data="stats" :timespan="timespan" />

    <k-retour-tabs
      tab="system"
      :tabs="tabs"
      :buttons="[
        {
          link: 'https://paypal.me/distantnative',
          theme: 'positive',
          target: '_blank',
          icon: 'heart',
          text: $t('retour.system.support'),
        },
      ]"
    />

    <k-stats :reports="reports" size="huge" />

    <!-- eslint-disable vue/no-v-html -->
    <k-text
      class="k-help"
      :html="
        $t('retour.system.docs', {
          docs: 'https://github.com/distantnative/retour-for-kirby',
        })
      "
    />
    <!-- eslint-enable vue/no-v-html -->
  </k-inside>
</template>

<script>
export default {
  props: {
    data: [Object, Array],
    stats: [Boolean, Array],
    tabs: Array,
    timespan: Object,
  },
  computed: {
    reports() {
      return [
        {
          label: this.$t("retour.system.redirects"),
          value: String(this.data.redirects),
        },
        {
          label: this.$t("retour.system.failures"),
          value: String(this.data.failures),
        },
        {
          label: this.$t("retour.system.deleteAfter"),
          value: this.$t("retour.system.deleteAfter.months", {
            count: this.data.deleteAfter,
          }),
        },
      ];
    },
  },
};
</script>

<style>
.k-retour-system-view .k-stats {
  margin-bottom: var(--spacing-3);
}
.k-retour-system-view .k-stats [data-theme] .k-stat-value {
  color: var(--theme);
}
</style>
