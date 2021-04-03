<template>
  <k-view class="tabs">
    <navigation v-if="hasLog" :tab="tab" :tabs="tabs" />
    <component :is="this.tab" class="tab" />
  </k-view>
</template>

<script>
import Navigation from "./Navigation.vue";
import Fails from "./Tabs/Fails.vue";
import Routes from "./Tabs/Routes.vue";
import System from "./Tabs/System.vue";

export default {
  components: {
    Navigation,
    Fails,
    Routes,
    System
  },
  computed: {
    hasLog() {
      return this.$store.state.retour.meta.hasLog !== false;
    },
    tab() {
      return this.$route.hash.slice(1) || "routes";
    },
    tabs() {
      let tabs    = [];
      const store = this.$store.state.retour;

      // Redirects
      const routes = store.data.redirects.length;

      tabs.push({
        name: "routes",
        label: this.$t("retour.routes"),
        icon: "undo",
        badge: routes || false
      });

      if (this.hasLog) {
        // Fails
        let fails = store.data.fails.length;
        if (fails >= 1000) {
          fails = (Math.floor(fails / 100) / 10) + "k";
        }

        tabs.push({
          name: "fails",
          label: this.$t("retour.failures"),
          icon: "alert",
          badge: fails || false
        });

        // System
        tabs.push({
          name: "system",
          label: this.$t("retour.system"),
          icon: "box",
          badge: false
        });
      }

      return tabs;
    }
  },
  watch: {
    tab: {
      handler() {
        const tab = this.tabs.filter(tab => tab.name === this.tab)[0];
        
        setTimeout(() => {
          this.$store.dispatch("breadcrumb", [{
            label: tab.label,
            link: "/plugins/retour#" + tab.name
          }]);
        }, 10);
      },
      immediate: true
    }
  },
}
</script>

<style>
.retour .tab {
  margin-top: 2rem;
}
</style>