<template>
  <div class="k-retour-view pb-24">

    <!-- full version -->
    <template v-if="true">
      <retour-stats />
      <k-tabs :tabs="tabs" :tab="tab" />
      <div class="p-6">
        <component :is="'retour-' + this.tab + '-tab'" />
      </div>
    </template>

    <!-- only routes -->
    <template v-else>
      <retour-routes-tab />
      <retour-settings-tab />
    </template>

  </div>
</template>

<script>
import permissions from "../mixins/permissions.js";

import Stats from "./Stats.vue";
import RoutesTab from "./RoutesTab.vue";
import FailuresTab from "./FailuresTab.vue";
import SystemTab from "./SystemTab.vue";

export default {
  mixins: [permissions],
  components: {
    "retour-stats": Stats,
    "retour-routes-tab": RoutesTab,
    "retour-failures-tab": FailuresTab,
    "retour-system-tab": SystemTab
  },
  computed: {
    data() {
      return this.$store.state.retour.data;
    },
    hasLogs() {
      return this.$store.getters["retour/hasLogs"];
    },
    tab() {
      return this.$route.hash.slice(1) || "routes";
    },
    tabs() {
      const routes = this.data.routes.length;
      const failures = this.data.failures.length;

      if (failures > 1000) {
        failures = (Math.floor(failures / 100) / 10) + "k";
      }

      return [
        {
          name: "routes",
          label: this.$t("retour.routes"),
          icon: "road-sign",
          badge: routes ? {
            count: routes,
            color: "focus"
          }: false
        },
        {
          name: "failures",
          label: this.$t("retour.failures"),
          icon: "alert",
          badge: failures ? {
            count: failures,
            color: "negative"
          } : false
        },
        {
          name: "system",
          label: this.$t("retour.system"),
          icon: "box",
          badge: this.$store.state.retour.system.update < 0 ? {
            count: 1,
            color: "positive"
          } : false
        }
      ];
    }
  },
  watch: {
    "$route.hash": {
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
  }
};
</script>
