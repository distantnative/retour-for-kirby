<template>
  <div class="retour-routes-tab">

    <!-- table -->
    <k-table
      :columns="columns"
      :options="options"
      :rows="rows"
      @cell="onCell"
      @header="onHeader"
      @option="onOption"
    >
      <template #cell="{ columnIndex, value }">
        <p class="k-table-cell-value">
          <template v-if="columnIndex === 'from' || columnIndex === 'to'">
            <retour-table-link-preview :value="value" />
          </template>

          <template v-else-if="columnIndex === 'status'">
            <retour-table-status-preview :value="value" />
          </template>

          <template v-else>
            {{ value }}
          </template>
        </p>
      </template>
    </k-table>

    <!-- dialog -->
    <k-form-drawer
      ref="drawer"
      v-model="row"
      :autofocus="row === null"
      :fields="fields"
      :submit-button="{ text: $t('save'), color: 'positive' }"
      :title="$t('retour.routes') + ' / ' + $t('edit')"
      size="large"
      @cancel="onCancel"
      @submit="onSubmit"
    />
  </div>
</template>

<script>
import TableLinkPreview from "./TableLinkPreview.vue";
import TableStatusPreview from "./TableStatusPreview.vue";

export default {
  components: {
    "retour-table-link-preview": TableLinkPreview,
    "retour-table-status-preview": TableStatusPreview
  },
  data() {
    return {
      row: null
    };
  },
  computed: {
    columns() {
      return {
        from: {
          label: this.$t("retour.redirects.from"),
          width: "1/3"
        },
        to: {
          label: this.$t("retour.redirects.to"),
          width: "1/3"
        },
        status: {
          label: this.$t("retour.redirects.status"),
          width: "1/12",
          align: "center"
        },
        hits: {
          label: this.$t("retour.hits"),
          width: "1/12",
          align: "right"
        },
        last: {
          label: this.$t("retour.hits.last"),
          width: "1/6"
        }
      };
    },
    fields() {
      return {
        from: {
          label: this.$t("retour.redirects.from"),
          type: "text",
          before: window.panel.site + "/",
          help: this.$t("retour.redirects.from.help", {
            docs: "https://distantnative.com/retour/docs"
          }),
          icon: "url",
          counter: false,
          required: true,
          width: "1/2"
        },
        to: {
          label: this.$t("retour.redirects.to"),
          type: "retour-target",
          help: this.$t("retour.redirects.to.help"),
          icon: "parent",
          counter: false,
          width: "1/2"
        },
        status: {
          label: this.$t("retour.redirects.status"),
          type: "retour-status",
          options: [
            { text: "––––", value: "–" },
            ...Object.keys(this.$store.state.retour.system.headers).map(code => ({
              text: code.substr(1) + " - " + this.$store.state.retour.system.headers[code],
              value: code.substr(1)
            }))
          ],
          help: this.$t("retour.redirects.status.help", {
            docs: "https://distantnative.com/retour/docs"
          }),
          empty: false,
          required: true,
          width: "1/2"
        }
      };
    },
    options() {
      return [
        { text: this.$t("edit"), icon: "edit", click: "edit" },
        { text: this.$t("remove"), icon: "trash", click: "remove" }
      ];
    },
    rows() {
      // TODO: remove when fixed in core
      return this.$store.state.retour.data.routes.map(route => {
        Object.keys(route).forEach(key => {
          route[key] = route[key] || "";
        });
        return route;
      });
    },
    site() {
      return window.panel.site;
    },
  },
  methods: {
    onCancel() {
      this.row = null;
    },
    onCell({ row }) {
      this.row = row;
      this.$refs.drawer.open();
    },
    onHeader() {

    },
    onNavigate() {

    },
    onOption(option, row, rowIndex) {
      switch (option) {
        case "edit":
          this.row = row;
          return this.$refs.drawer.open();
        case "remove":
            return;
      }
    },
    onSubmit() {

    }
  }
}
</script>
