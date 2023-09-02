<template>
  <k-inside class="k-retour-view k-retour-system-view">
    <template #topbar>
      <k-retour-timespan :timespan="timespan" />
    </template>

    <k-retour-stats v-if="stats" :data="stats" :timespan="timespan" />
    <k-retour-tabs :tabs="tabs" tab="system" />

    <k-stats :reports="reports" />

    <!-- eslint-disable vue/no-v-html -->
    <k-text
      class="k-help"
      v-html="
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
          value: this.data.redirects,
        },
        {
          label: this.$t("retour.system.failures"),
          value: this.data.failures,
        },
        {
          label: this.$t("retour.system.deleteAfter"),
          value: this.$t("retour.system.deleteAfter.months", {
            count: this.data.deleteAfter,
          }),
        },
        {
          label: this.$t("retour.system.support"),
          value: `💛 ${this.$t("retour.system.support.donate")}`,
          link: "https://paypal.me/distantnative",
          theme: "positive",
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
