<template>
  <div class="k-retour-view">
    <stats />
    <entries />
    <settings />
  </div>
</template>

<script>
import permissions from "../mixins/permissions"

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
  }
}
</script>

<style>
.k-retour-view > .k-view {
  padding-top:    1.5rem;
  padding-bottom: 1.5rem;
}
</style>

