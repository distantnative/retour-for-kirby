<template>
  <retour-table
    :columns="columns"
    :empty="$t('retour.routes.empty')"
    :options="options"
    :rows="rows"
    tab="routes"
    @cell="onCell"
    @option="onOption"
  >
    <!-- add button -->
    <template v-if="canUpdate" #button>
      <k-button
        :text="$t('retour.routes.add')"
        icon="add"
        @click="onOption('add')"
      />
    </template>

    <template #dialogs>
      <!-- add dialog -->
      <k-form-drawer
        ref="addDialog"
        v-model="row"
        :autofocus="true"
        :fields="fields"
        :loading="isLoading"
        :submit-button="{ text: $t('add'), color: 'positive' }"
        :title="$t('retour.routes') + ' / ' + $t('add')"
        @cancel="onCancel"
        @submit="onAdd"
      />

      <!-- edit dialog -->
      <k-form-drawer
        ref="editDialog"
        v-model="row"
        :autofocus="false"
        :fields="fields"
        :loading="isLoading"
        :submit-button="{ text: $t('save'), color: 'positive' }"
        :title="$t('retour.routes') + ' / ' + $t('edit')"
        @cancel="onCancel"
        @submit="onEdit"
      />

      <!-- remove dialog -->
      <k-dialog
        ref="removeDialog"
        :loading="isLoading"
        :submit-button="{
          text: $t('delete'),
          icon: 'trash',
          color: 'negative'
        }"
        @submit="onRemove"
      >
        <k-text>{{ $t('field.structure.delete.confirm') }}</k-text>
      </k-dialog>
    </template>

  </retour-table>
</template>

<script>
import permissions from "../mixins/permissions.js";

import Table from "./Table.vue";

export default {
  mixins: [permissions],
  components: {
    "retour-table": Table
  },
  data() {
    return {
      row: {},
      rowIndex: null,
      after: null,
      isLoading: false
    };
  },
  computed: {
    columns() {
      return {
        from: {
          label: this.$t("retour.routes.fields.from"),
          type: "link",
          filter: true,
          width: "15/40"
        },
        to: {
          label: this.$t("retour.routes.fields.to"),
          type: "link",
          filter: true,
          width: "15/40"
        },
        status: {
          label: this.$t("retour.routes.fields.status"),
          type: "status",
          width: "1/10",
          align: "center"
        },
        priority: {
          label: this.$t("retour.routes.fields.priority.abbr"),
          type: "priority",
          width: "1/20"
        },
        hits: {
          label: this.$t("retour.hits"),
          width: "1/10",
          type: "count",
          align: "right"
        }
      };
    },
    fields() {
      return {
        from: {
          label: this.$t("retour.routes.fields.from"),
          type: "text",
          before: window.panel.site + "/",
          help: this.$t("retour.routes.fields.from.help", {
            docs: "https://distantnative.com/retour/docs"
          }),
          icon: "url",
          counter: false,
          required: true,
          width: "1/2"
        },
        to: {
          label: this.$t("retour.routes.fields.to"),
          type: "retour-target",
          help: this.$t("retour.routes.fields.to.help"),
          icon: "parent",
          counter: false,
          width: "1/2"
        },
        status: {
          label: this.$t("retour.routes.fields.status"),
          type: "retour-status",
          options: [
            ...Object.keys(this.$store.state.retour.system.headers).map(code => ({
              text: code.substr(1) + " - " + this.$store.state.retour.system.headers[code],
              value: code.substr(1)
            }))
          ],
          help: this.$t("retour.routes.fields.status.help", {
            docs: "https://distantnative.com/retour/docs"
          }),
          width: "1/2"
        },
        priority: {
          type: "toggle",
          label: this.$t("retour.routes.fields.priority"),
          icon: "bolt",
          help: this.$t("retour.routes.fields.priority.help"),
          width: "1/2",
        },
        comment: {
          type: "textarea",
          label: this.$t("retour.routes.fields.comment"),
          buttons: false,
          help: this.$t("retour.routes.fields.comment.help")
        }
      };
    },
    options() {
      if (this.canUpdate === false) {
        return false;
      }

      return [
        { text: this.$t("edit"), icon: "edit", click: "edit" },
        { text: this.$t("remove"), icon: "trash", click: "remove" }
      ];
    },
    rows() {
      return this.$store.state.retour.data.routes;
    },
    site() {
      return window.panel.site;
    },
  },
  created() {
    this.$events.$on("retour.resolve", this.resolve);
  },
  destroyed() {
    this.$events.$off("retour.resolve", this.resolve);
  },
  methods: {
    async onAdd() {
      let rows = this.$helper.clone(this.rows);
      rows.splice(rows.length, 0, this.row);
      await this.onUpdate(rows);
      this.$refs.addDialog.close();
    },
    onCancel() {
      this.row = {};
      this.rowIndex = null;
      this.after = null;
    },
    onCell({ row, rowIndex, columnIndex }) {
      if (this.canUpdate === false) {
        return;
      }

      this.onOption("edit", row, rowIndex);
      setTimeout(() => {
        this.$refs.editDialog.focus(columnIndex);
      }, 50);
    },
    async onEdit() {
      let rows = this.$helper.clone(this.rows);
      let row  = this.$helper.clone(this.row);
      rows.splice(this.rowIndex, 1, row);
      await this.onUpdate(rows);
      this.$refs.editDialog.close();
    },
    onOption(option, row = {}, rowIndex) {
      this.row = this.$helper.clone(row);
      this.rowIndex = rowIndex;
      this.$refs[option + "Dialog"].open();
    },
    async onRemove() {
      let rows = this.$helper.clone(this.rows);
      rows.splice(this.rowIndex, 1);
      await this.onUpdate(rows);
      this.$refs.removeDialog.close();
    },
    async onResolve() {
      try {
        await this.$api.post("retour/log/resolve", {
          path: this.row.from
        });

        const calls = [
          this.$store.dispatch("retour/failures"),
          this.$store.dispatch("retour/stats")
        ];

        await Promise.all(calls);

      } catch (error) {
        this.$store.dispatch("notification/error", error);
      }
    },
    async onUpdate(rows) {
      this.isLoading = true;

      try {
        await this.$api.patch("retour/routes", rows);
        await this.$store.dispatch("retour/routes");

        if (this.after) {
          await this.after();
        }

      } catch (error) {
        this.$store.dispatch("notification/error", error);

      } finally {
        this.isLoading = false;
        this.onCancel();
      }
    },
    resolve(row) {
      this.onOption("add", row);
      this.after = this.onResolve;
    },
  }
}
</script>
