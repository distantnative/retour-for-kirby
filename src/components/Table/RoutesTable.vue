<template>
  <retour-table
    :columns="columns"
    :empty="$t('retour.routes.empty')"
    :label="label"
    :options="options"
    :rows="rows"
    :type="type"
    @cell="onCell"
    @input="onCellUpdate"
    @option="onOption"
  >
    <!-- button -->
    <template #button>
      <slot name="button" :onOption="onOption" />
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
import permissions from "../../mixins/permissions.js";

import Table from "./Table.vue";

export default {
  mixins: [permissions],
  components: {
    "retour-table": Table
  },
  props: {
    canEdit: Boolean,
    label: String,
    options: Array,
    rows: Array,
    type: String
  },
  data() {
    return {
      isLoading: false,
      row: {},
      rowIndex: null,
      after: null
    }
  },
  computed: {
    columns() {
      return {
        active: {
          label: " ",
          type: "toggle",
          width: "1/20"
        },
        from: {
          label: this.$t("retour.routes.from"),
          type: "link",
          filter: true,
          width: "7/20"
        },
        to: {
          label: this.$t("retour.routes.to"),
          type: "link",
          filter: true,
          width: "7/20"
        },
        status: {
          label: this.$t("retour.routes.status"),
          type: "status",
          width: "2/20"
        },
        priority: {
          label: this.$t("retour.routes.priority.abbr"),
          type: "priority",
          width: "1/20"
        },
        hits: {
          label: this.$t("retour.hits"),
          width: "2/20",
          type: "count"
        }
      };
    },
    fields() {
      return {
        from: {
          label: this.$t("retour.routes.from"),
          type: "text",
          before: window.panel.site + "/",
          help: this.$t("retour.routes.from.help", {
            docs: "https://distantnative.com/retour/docs"
          }),
          icon: "url",
          counter: false,
          required: true,
          width: "1/2"
        },
        to: {
          label: this.$t("retour.routes.to"),
          type: "retour-destination",
          help: this.$t("retour.routes.to.help"),
          icon: "parent",
          counter: false,
          width: "1/2"
        },
        status: {
          label: this.$t("retour.routes.status"),
          type: "retour-status",
          options: [
            ...Object.keys(this.$store.state.retour.system.headers).map(code => ({
              text: code.substr(1) + " - " + this.$store.state.retour.system.headers[code],
              value: code.substr(1)
            }))
          ],
          empty: false,
          required: true,
          help: this.$t("retour.routes.status.help", {
            docs: "https://distantnative.com/retour/docs"
          }),
          width: "1/2"
        },
        priority: {
          type: "toggle",
          label: this.$t("retour.routes.priority"),
          icon: "bolt",
          help: this.$t("retour.routes.priority.help"),
          width: "1/2",
        },
        active: {
          label: " ",
          type: "info",
          text: "This route is <b>not active</b>. Use the toggle in the table to activate it.",
          hidden: this.row.active
        },
        comment: {
          type: "textarea",
          label: this.$t("retour.routes.comment"),
          buttons: false,
          help: this.$t("retour.routes.comment.help")
        }
      };
    }
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
    onCell({ row, rowIndex, column, columnIndex }) {
      if (this.canEdit === false || column.type === "toggle") {
        return;
      }

      this.onOption("edit", row, rowIndex);
      setTimeout(() => {
        this.$refs.editDialog.focus(columnIndex);
      }, 50);
    },
    async onCellUpdate(rows) {
      // TODO: see if that's a bug in Kirby itself
      if( Array.isArray(rows) === false) {
        return;
      }
      await this.onUpdate(rows);
    },
    async onEdit() {
      let rows = this.$helper.clone(this.rows);
      let row  = this.$helper.clone(this.row);
      rows.splice(this.rowIndex, 1, row);
      await this.onUpdate(rows);
      this.$refs.editDialog.close();
    },
    onOption(option, row = {}, rowIndex) {
      switch (option) {
        case "move":
          return this.$events.$emit("retour.move", row);
        default:
          this.row = this.$helper.clone(row);
          this.rowIndex = rowIndex;
          this.$refs[option + "Dialog"].open();
      }
    },
    async onRemove() {
      let rows = this.$helper.clone(this.rows);
      rows.splice(this.rowIndex, 1);
      await this.onUpdate(rows);
      this.$refs.removeDialog.close();
    },
    async onUpdate(rows) {
      this.isLoading = true;

      try {
        await this.$api.patch("retour/routes/" + this.type, rows);
        await this.$store.dispatch("retour/routes", this.type);

        if (this.after) {
          await this.after(this.row);
        }

        this.$store.dispatch("notification/success");

      } catch (error) {
        this.$store.dispatch("notification/error", error);

      } finally {
        this.isLoading = false;
        this.onCancel();
      }
    },
    insert(row, after) {
      row.active = true;
      this.after = after;
      this.onOption("add", row);
    }
  }
}
</script>
