<template>
  <div class="k-retour-view">
    <stats v-if="hasLogs" />
    <tables />
    <settings v-if="hasLogs" />
  </div>
</template>

<script>
import {permissions} from "./helpers.js";

import Stats from "./Sections/Stats.vue";
import Tables from "./Sections/Tables.vue";
import Settings from "./Sections/Settings.vue";

export default {
  mixins: [permissions],
  components: {
    Stats,
    Tables,
    Settings,
  },
  mounted() {
    if (this.canAccess === false) {
      this.$router.push("/");
    }

    this.$store.dispatch("retour/init");
    this.$store.dispatch("retour/load");
  },
  computed: {
    hasLogs() {
      return this.$store.state.retour.options.logs;
    }
  }
}
</script>

<style>
.k-retour-view > .k-view {
  padding-top: 1.5rem;
  padding-bottom: 1.5rem;
}
</style>

