<template>
  <list
    :columns="columns"
    :rows="rows"
    :options="options"
    :empty="$t('retour.routes.empty')"
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
      <k-form-dialog
        ref="entry"
        v-model="row"
        :fields="fields"
        :novalidate="false"
        size="huge"
        @cancel="onClose"
        @submit="onSave"
      />

      <!-- remove -->
      <k-remove-dialog
        ref="remove"
        :submit-button="$t('delete')"
        @submit="onRemove"
      >
        <k-text>{{ $t('field.structure.delete.confirm') }}</k-text>
      </k-remove-dialog>
    </template>

  </list>
</template>

<script>
import permissions from "../../mixins/permissions.js";
import List from "../List/List.vue";

export default {
  mixins: [permissions],
  components: {
    List
  },
  data() {
    return {
      row: {},
      rowIndex: null,
      completion: null,
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
    fields() {
      const site = window.panel.site.replace(/^(http\:\/\/|https\:\/\/)/,"").replace(/^(www\.)/,"") + "/";
      const headers = Object.keys(this.headers).map(code => ({
        text: code.substr(1) + " - " + this.headers[code],
        value: code.substr(1)
      }));

      return {
        from: {
          label: this.$t("retour.routes.from"),
          type: "text",
          before: site,
          help: this.$t("retour.routes.from.help", {
            docs: "https://distantnative.com/retour/docs"
          }),
          counter: false,
          required: true
        },
        to: {
          label: this.$t("retour.routes.to"),
          type: "rt-destination",
          help: this.$t("retour.routes.to.help"),
          counter: false
        },
        status: {
          label: this.$t("retour.routes.status"),
          type: "rt-status",
          options: headers,
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
    },
    headers() {
      return this.$store.state.retour.meta.headers;
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
      return Object.values(this.$store.state.retour.data.redirects);
    }
  },
  created() {
    this.$events.$on("retour.resolve", this.resolve);
  },
  destroyed() {
    this.$events.$off("retour.resolve", this.resolve);
  },
  methods: {
    onCell({ row, rowIndex, columnIndex }) {
      if (this.canUpdate === false) {
        return;
      }

      this.onOption("edit", row, rowIndex);
      setTimeout(() => {
        this.$refs.entry.$refs.form.focus(columnIndex);
      }, 25);
    },
    onClose() {
      this.row = {};
      this.rowIndex = null;
      this.completion = null;
    },
    onOption(option, row = {}, rowIndex = null) {
      switch (option) {
        case "add":
        case "edit":
          this.row = this.$helper.clone(row);
          this.rowIndex = rowIndex;
          this.$refs.entry.open();
          break;
        case "remove":
          this.rowIndex = rowIndex;
          this.$refs.remove.open();
          break;
      }
    },
    async onRemove() {
      this.$refs.remove.close();

      try {
        await this.$api.delete("retour/redirects/" + this.rowIndex);
        await this.$store.dispatch("retour/data");
        this.$store.dispatch("notification/success", ":)");

      } catch (error) {
        this.$store.dispatch("notification/error", error);
      }
    },
    async onResolve(row) {
      try {
        await this.$api.post("retour/log/resolve", { path: row.from });
        await this.$store.dispatch("retour/data");

      } catch (error) {
        this.$store.dispatch("notification/error", error.message);
      }
    },
    async onSave() {
      const hasErrors = Object.values(this.$refs.entry.$refs.form.$refs.fields.errors).filter(x => x.$invalid).length > 0;

      if (hasErrors) {
        return this.$refs.entry.error(this.$t("field.required"));
      }

      this.$refs.entry.close();

      try {
        if (this.rowIndex === null) {
          await this.$api.post("retour/redirects", this.row);

        } else {
          await this.$api.patch("retour/redirects/" + this.rowIndex, this.row);
        }

        if (this.completion) {
          await this.completion(this.row);
        }

        this.$store.dispatch("notification/success", ":)");

      } catch (error) {
        this.$store.dispatch("notification/error", error.message);

      } finally {
        await this.$store.dispatch("retour/data");
        this.onClose();
      }
    },
    async resolve(row) {
      this.completion = this.onResolve;
      this.onOption("add", row);
    },
  }
}
</script>

<style>
.k-dialog[data-size="huge"] {
  width: 95vw;
  max-width: 50rem;
}
</style>
