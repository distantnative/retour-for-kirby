<template>
  <div class="k-retour-view">
    <stats v-if="hasLogs" />
    <entries />
    <settings v-if="hasLogs" />
  </div>
</template>

<script>
import {permissions} from "./helpers.js";

import Entries from "./Sections/Entries.vue";
import Settings from "./Sections/Settings.vue";
import Stats from "./Sections/Stats.vue";

export default {
  mixins: [permissions],
  components: {
    Entries,
    Settings,
    Stats
  },
  mounted() {
    if (this.canAccess === false) {
      this.$router.push("/");
    }

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
  padding-top:    1.5rem;
  padding-bottom: 1.5rem;
}
</style>

