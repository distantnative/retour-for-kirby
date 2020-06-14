<template>
  <div>
    <k-box v-if="recent" theme="notice" class="mb-6">
      <b>{{ recent }} inactive route(s)</b> for tracked changes. Either activate or dismiss by removing the route.
    </k-box>

    <retour-routes-table
      :canEdit="false"
      :label="$t('retour.tracked')"
      :options="options"
      :rows="rows"
      type="tracked"
    />
  </div>
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
          // { text: this.$t("retour.tracked.move"), icon: "parent", click: "insert" },
          { text: this.$t("remove"), icon: "trash", click: "remove" }
        ];
      }
    },
    recent() {
      return this.rows.filter(row => row.active === false).length;
    },
    rows() {
      return this.$store.state.retour.data.tracked;
    }
  }
}
</script>

<style lang="scss">
.retour-view .k-box[data-theme="notice"] {
  background: lighten(#f7e2b8, 8%);
  border-left: 2px solid #f0c674;
}
</style>
