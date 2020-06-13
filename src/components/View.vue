<template>
  <div class="k-retour-view pb-24">

    <!-- full version -->
    <template v-if="hasLog">
      <retour-stats />
      <k-tabs :tabs="tabs" :tab="tab" />
      <div class="p-6">
        <component :is="'retour-' + this.tab + '-tab'" />
      </div>
    </template>

    <!-- only routes -->
    <template v-else>
      <div class="p-6 pt-16">
        <retour-routes-tab class="mb-8" />
        <retour-system-tab />
      </div>
    </template>

  </div>
</template>

<script>
import permissions from "../mixins/permissions.js";
import tabs from "../config/tabs.js";

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
    hasLog() {
      return this.$store.state.retour.system.hasLog;
    },
    tab() {
      return this.$route.hash.slice(1) || "routes";
    },
    tabs() {
      return tabs(this);
    }
  },
  watch: {
    "$route.hash": {
      handler() {
        if (this.hasLog) {
          this.$emit("breadcrumb", [
            { text: this.tabs.filter(tab => tab.name === this.tab)[0].label }
          ]);
        }
      },
      immediate: true
    }
  },
  created() {
    this.$store.dispatch("retour/load");
  }
};
</script>
