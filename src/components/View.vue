<template>
  <div class="k-retour-view pb-24">
    <retour-stats />
    <k-tabs :tabs="tabs" :tab="tab" />
    <div class="p-6">
      <component :is="'retour-' + this.tab + '-tab'" />
    </div>
  </div>
</template>

<script>
import { permissions } from "../helpers.js";

import Stats from "./Stats.vue";
import RoutesTab from "./RoutesTab.vue";
import FailuresTab from "./FailuresTab.vue";

export default {
  mixins: [permissions],
  components: {
    "retour-stats": Stats,
    "retour-routes-tab": RoutesTab,
    "retour-failures-tab": FailuresTab,
    "retour-settings-tab": SettingsTab
  },
  computed: {
    hasLogs() {
      // return this.$store.state.retour.options.logs;
    },
    tab() {
      return this.$route.hash.slice(1) || "routes";
    },
    tabs() {
      return [
        {
          name: "routes",
          label: this.$t("retour.routes"),
          icon: "road-sign",
          badge: {
            count: this.$store.state.retour.data.routes.length,
            color: "focus"
          }
        },
        {
          name: "failures",
          label: this.$t("retour.failures"),
          icon: "alert",
          badge: {
            count: this.$store.state.retour.data.failures.length,
            color: "negative"
          }
        },
        {
          name: "settings",
          label: this.$t("retour.settings"),
          icon: "box"
        }
      ];
    }
  },
  watch: {
    tab: {
      handler() {
        this.$emit("breadcrumb", [
          { text: this.tabs.filter(tab => tab.name === this.tab)[0].label }
        ]);
      },
      immediate: true
    }
  },
  created() {
    this.$store.dispatch("retour/load");
  },
};
</script>
