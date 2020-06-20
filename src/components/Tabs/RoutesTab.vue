<template>
  <retour-routes-table
    ref="table"
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
import permissions from "../../mixins/permissions.js";

import RoutesTable from "../Table/RoutesTable.vue";

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
      return this.$store.state.retour.data.routes;
    }
  },
  created() {
    this.$events.$on("retour.resolve", this.resolve);
  },
  destroyed() {
    this.$events.$off("retour.resolve", this.resolve);
  },
  methods: {
    resolve(row) {
      this.$refs.table.insert(row, this.onResolve);
    },
    async onResolve(row) {
      try {
        await this.$api.post("retour/log/resolve", {
          path: row.from
        });

        const calls = [
          this.$store.dispatch("retour/failures"),
          this.$store.dispatch("retour/stats")
        ];

        await Promise.all(calls);

      } catch (error) {
        this.$store.dispatch("notification/error", error);
      }
    }
  }
}
</script>
