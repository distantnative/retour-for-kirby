<template>
  <div class="rt-view">

    <!-- loader -->
    <k-loader v-if="isLoading" />

    <template v-else>
      <!-- stats -->
      <retour-stats v-if="hasLog" />

      <!-- tabs -->
      <k-view class="rt-tabs">
        <k-tabs :tabs="tabs" :tab="tab" />
        <component :is="'retour-' + this.tab + '-tab'" />
      </k-view>
    </template>
  </div>
</template>

<script>
import permissions from "../mixins/permissions.js";
import tabs from "../config/tabs.js";

import Stats from "./Stats.vue";

import FailuresTab from "./Tabs/FailuresTab.vue";
import RoutesTab from "./Tabs/RoutesTab.vue";
import SystemTab from "./Tabs/SystemTab.vue";

export default {
  mixins: [permissions],
  components: {
    "retour-stats": Stats,
    "retour-failures-tab": FailuresTab,
    "retour-routes-tab": RoutesTab,
    "retour-system-tab": SystemTab
  },
  data() {
    return {
      isLoading: true
    };
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
        const tab = this.tabs.filter(tab => tab.name === this.tab)[0];
        this.$emit("breadcrumb", [{ text: tab.label }]);
      },
      immediate: true
    }
  },
  async created() {
    if (this.canAccess === false) {
      this.$router.push("/");
    }

    this.isLoading = true;
    await this.$store.dispatch("retour/load");
    this.isLoading = false;
  }
};
</script>

<style lang="scss">
.rt-view {
  padding-bottom: 6rem;
}
.rt-view > .k-loader {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}
.rt-tabs {
  margin-top: 1.5rem;
}
</style>
