<template>
  <retour-routes-table
    :canEdit="canUpdate"
    :label="$t('retour.routes')"
    :options="options"
    :rows="rows"
    type="manual"
  >
    <!-- add button -->
    <template v-if="canUpdate" #button="{ onOption }">
      <k-button
        :text="$t('retour.routes.add')"
        icon="add"
        @click="onOption('add', { active: true })"
      />
    </template>
  </retour-routes-table>
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
          { text: this.$t("edit"), icon: "edit", click: "edit" },
          { text: this.$t("remove"), icon: "trash", click: "remove" }
        ];
      }
    },
    rows() {
      return this.$store.state.retour.data.manual;
    }
  },
  created() {
    this.$events.$on("retour.resolve", this.resolve);
  },
  destroyed() {
    this.$events.$off("retour.resolve", this.resolve);
  },
  methods: {
    onResolve() {

    }
  }
}
</script>
