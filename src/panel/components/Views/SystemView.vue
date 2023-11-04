<template>
  <k-retour-view v-bind="$props" class="k-retour-system-view">
    <template #buttons>
      <k-button
        link="https://paypal.me/distantnative"
        icon="heart"
        size="sm"
        target="_blank"
        theme="positive"
        variant="filled"
      >
        {{ $t("retour.system.support") }}
      </k-button>
    </template>

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
  </k-retour-view>
</template>

<script>
import { props as View } from "./View.vue";

export default {
  mixins: [View],
  computed: {
    reports() {
      return [
        {
          label: this.$t("retour.system.redirects"),
          value: String(this.data.redirects),
          icon: "shuffle"
        },
        {
          label: this.$t("retour.system.failures"),
          value: String(this.data.failures),
          icon: "cloud-off"
        },
        {
          label: this.$t("retour.system.deleteAfter"),
          value: this.$t("retour.system.deleteAfter.months", {
            count: this.data.deleteAfter,
          }),
          icon: "trash"
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
</style>
