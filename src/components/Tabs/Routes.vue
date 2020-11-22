<template>
  <rt-table
    :label="$t('retour.routes')"
    :empty="$t('retour.routes.empty')"
    :columns="columns"
    :rows="rows"
    :options="options"
    type="routes"
    @cell="onCell"
    @option="onOption"
  >
    <!-- button -->
    <template v-if="canUpdate" #button>
      <k-button
        icon="add"
        @click="onOption('add')"
      >
        {{ $t('add') }}
      </k-button>
    </template>

    <!-- dialogs -->
    <template #dialogs>
      <!-- add/edit -->
      <k-form-drawer
        ref="drawer"
        :title="title"
        :tabs="tabs"
        :value="row"
        icon="undo"
        @input="onInput"
        @close="onClose"
      >
        <template #options>
          <k-button
            class="k-drawer-option"
            icon="check"
            theme="positive"
            @click="save"
          />
        </template>
      </k-form-drawer>

      <!-- remove -->
      <k-remove-dialog
        ref="remove"
        :submit-button="$t('delete')"
        @submit="remove"
      >
        <k-text>{{ $t('field.structure.delete.confirm') }}</k-text>
      </k-remove-dialog>
    </template>

  </rt-table>
</template>

<script>
import permissions from "../../mixins/permissions.js";

export default {
  mixins: [permissions],
  data() {
    return {
      row: {},
      rowIndex: null,
      completion: null
    };
  },
  computed: {
    columns() {
      return {
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
          width: "1/10"
        },
        priority: {
          label: this.$t("retour.routes.priority.abbr"),
          type: "priority",
          width: "1/20"
        },
        hits: {
          label: this.$t("retour.hits"),
          width: "1/10",
          type: "count"
        }
      };
    },
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
    },
    tabs() {
      return [
        {
          fields: {
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
              type: "rt-destination",
              help: this.$t("retour.routes.to.help"),
              icon: "parent",
              counter: false,
              width: "1/2"
            },
            status: {
              label: this.$t("retour.routes.status"),
              type: "rt-status",
              options: [
                ...Object.keys(this.$store.state.retour.system.headers).map(code => ({
                  text: code.substr(1) + " - " + this.$store.state.retour.system.headers[code],
                  value: code.substr(1)
                }))
              ],
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
            comment: {
              type: "textarea",
              label: this.$t("retour.routes.comment"),
              buttons: false,
              help: this.$t("retour.routes.comment.help")
            }
          }
        }
      ]
    },
    title() {
      return this.$t("retour.routes") + " / " + this.$t(this.rowIndex === null ? "add" : "edit");
    }
  },
  created() {
    this.$events.$on("retour.resolve", this.onResolve);
  },
  destroyed() {
    this.$events.$off("retour.resolve", this.onResolve);
  },
  methods: {
    onCell({ row, rowIndex, columnIndex }) {
      if (this.canUpdate === false) {
        return;
      }

      this.onOption("edit", row, rowIndex);
      setTimeout(() => {
        this.$refs.drawer.$refs.form.focus(columnIndex);
      }, 25);
    },
    onClose() {
      this.row = {};
      this.rowIndex = null;
      this.completion = null;
    },
    onInput(input) {
      this.row = input;
    },
    onOption(option, row = {}, rowIndex = null) {
      switch (option) {
        case "add":
        case "edit":
          this.row = this.$helper.clone(row);
          this.rowIndex = rowIndex;
          this.$refs.drawer.open();
          break;
      }
    },
    onResolve(row) {
      this.completion = this.resolve;
      this.onOption("add", row);
    },
    async remove() {
      let rows = this.$helper.clone(this.rows);
      rows.splice(this.rowIndex, 1);
      await this.update(rows);
      this.$refs.remove.close();
    },
    async resolve(row) {
      try {
        await this.$api.post("retour/log/resolve", { path: row.from });
        await this.$store.dispatch("retour/load", true);

      } catch (error) {
        this.$store.dispatch("notification/error", error);
      }
    },
    async save() {
      let rows = this.$helper.clone(this.rows);
      let row  = this.$helper.clone(this.row);

      if (this.rowIndex === null) {
        rows.splice(rows.length, 0, row);
      } else {
        rows.splice(this.rowIndex, 1, row);
      }

      await this.update(rows);

      if (this.completion) {
        await this.completion(row);
      }

      this.$refs.drawer.close();
    },
    async update(rows) {
      try {
        // update rows
        await this.$api.patch("retour/routes", rows);

        // reload rows
        await this.$store.dispatch("retour/routes");

        this.$store.dispatch("notification/success");

      } catch (error) {
        this.$store.dispatch("notification/error", error);
      }
    }
  }
}
</script>
