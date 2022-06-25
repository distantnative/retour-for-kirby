<template>
  <k-view class="retour-plugin-tab">
    <header>
      <h3><k-icon type="road-sign" /> Retour for Kirby</h3>
    </header>

    <k-stats :reports="reports" />

    <footer>
      <!-- eslint-disable vue/no-v-html -->
      <k-text
        theme="help"
        v-html="
          $t('retour.system.docs', {
            docs: 'https://github.com/distantnative/retour-for-kirby',
          })
        "
      />
      <!-- eslint-enable vue/no-v-html -->
    </footer>
  </k-view>
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
.retour-plugin-tab header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.75rem;
}
.retour-plugin-tab h3 {
  display: flex;
  align-items: center;
  font-weight: bold;
}
.retour-plugin-tab h3 .k-icon {
  margin-right: 0.75rem;
}
.retour-plugin-tab .k-stats [data-theme] .k-stat-value {
  color: var(--theme);
}
.retour-plugin-tab .k-stats {
  margin-bottom: 0.75rem;
}
</style>
