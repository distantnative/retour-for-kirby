<template>
  <div class="rt-view">
    <k-loader v-if="isLoading" />

    <template v-else>
      <rt-stats v-if="hasLog" />
      <rt-tabs />
    </template>
  </div>
</template>

<script>
import permissions from "../mixins/permissions.js";

import Stats from "./Stats.vue";
import Tabs from "./Tabs.vue";

export default {
  mixins: [permissions],
  components: {
    "rt-stats": Stats,
    "rt-tabs": Tabs
  },
  data() {
    return {
      isLoading: true
    };
  },
  computed: {
    hasLog() {
      return this.$store.state.retour.system.hasLog;
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
</style>
