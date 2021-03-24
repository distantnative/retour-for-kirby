<template>
  <div class="retour">
    <stats v-if="hasLog" />
    <tabs />
  </div>
</template>

<script>
import permissions from "../mixins/permissions.js";

import Stats from "./Stats.vue";
import Tabs from "./Tabs.vue";

export default {
  mixins: [permissions],
  components: {
    Stats,
    Tabs
  },
  computed: {
    hasLog() {
      return this.$store.state.retour.meta.hasLog !== false;
    },
  },
  async created() {
    if (this.canAccess === false) {
      this.$router.push("/");
    }

    await this.$store.dispatch("retour/init");
  }
}
</script>