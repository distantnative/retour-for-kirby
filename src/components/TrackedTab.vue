<template>
  <retour-routes-table
    :canEdit="false"
    :label="$t('retour.tracked')"
    :options="options"
    :rows="rows"
    tab="routes"
  />
</template>

<script>
import permissions from "../mixins/permissions.js";

import RoutesTable from "./RoutesTable.vue";

export default {
  mixins: [permissions],
  components: {
    "retour-routes-table": RoutesTable
  },
  computed: {
    options() {
      if (this.canUpdate !== false) {
        return [
          { text: this.$t("retour.tracked.add"), icon: "parent", click: "insert" },
          { text: this.$t("remove"), icon: "trash", click: "remove" }
        ];
      }
    },
    rows() {
      return this.$store.state.retour.data.tracked;
    }
  }
}
</script>
