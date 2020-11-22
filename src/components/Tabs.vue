<template>
  <k-view class="rt-tabs">
    <k-tabs :tabs="tabs" :tab="tab" />
    <component :is="'rt-' + this.tab + '-tab'" class="rt-tab" />
  </k-view>
</template>

<script>
import FailuresTab from "./Tabs/FailuresTab.vue";
import RoutesTab from "./Tabs/RoutesTab.vue";
import SystemTab from "./Tabs/SystemTab.vue";

export default {
  components: {
    "rt-failures-tab": FailuresTab,
    "rt-routes-tab": RoutesTab,
    "rt-system-tab": SystemTab
  },
  computed: {
    tab() {
      return this.$route.hash.slice(1) || "routes";
    },
    tabs() {
      let tabs    = [];
      const store = this.$store.state.retour;

      // routes
      const routes = store.data.routes.length;

      tabs.push({
        name: "routes",
        label: this.$t("retour.routes"),
        icon: "undo",
        badge: routes || false
      });

      // failures
      if (store.system.hasLog) {
        const failures = store.data.failures.length;

        if (failures > 1000) {
          failures = (Math.floor(failures / 100) / 10) + "k";
        }

        tabs.push({
          name: "failures",
          label: this.$t("retour.failures"),
          icon: "alert",
          badge: failures || false
        });
      }

      // system
      tabs.push({
        name: "system",
        label: this.$t("retour.system"),
        icon: "box",
        badge: store.system.update < 0 ? 1 : false
      });

      return tabs;
    }
  },
  watch: {
    tab: {
      handler() {
        const tab = this.tabs.filter(tab => tab.name === this.tab)[0];
        this.$store.dispatch("breadcrumb", [{
          label: tab.label,
          link: "plugins/retour#" + tab.name
        }]);
      },
      immediate: true
    }
  },
}
</script>

<style lang="scss">
.rt-tabs {
  margin-top: 1.5rem;
}
.rt-tab {
  padding-top: 1.5rem;
}
.rt-view .k-tab-button[href="#system"] .k-tabs-badge {
  color: var(--color-positive);
}
.rt-view .k-tab-button[href="#failures"] .k-tabs-badge {
  color: var(--color-negative);
}
.rt-view .k-tab-button[href="#routes"] .k-tabs-badge {
  color: var(--color-focus);
}
</style>
