<template>
  <section class="retour-plugin-tab">
    <k-stats :reports="reports" />

    <footer>
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
    </footer>
  </section>
</template>

<script>
export default {
  props: {
    data: Object,
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
.retour-plugin-tab .k-stats [data-theme] .k-stat-value {
  color: var(--theme);
}
.retour-plugin-tab .k-stats {
  margin-bottom: var(--spacing-3);
}
</style>
