<template>
  <div class="retour-view pb-24">

    <!-- loader -->
    <k-loader v-if="isLoading" />

    <!-- full version -->
    <template v-else-if="hasLog">
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
    isLoading() {
      return this.$store.state.retour.system.isLoading;
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
        this.$emit("breadcrumb", [
          { text: this.tabs.filter(tab => tab.name === this.tab)[0].label }
        ]);
      },
      immediate: true
    }
  },
  created() {
    if (this.canAccess === false) {
      this.$router.push("/");
    }

    this.$store.dispatch("retour/load");
  }
};
</script>

<style lang="scss">
.retour-view .k-loader {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}
</style>
