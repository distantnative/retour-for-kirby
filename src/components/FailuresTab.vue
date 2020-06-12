<template>
  <div class="retour-failures-tab">

     <!-- header -->
    <header class="k-header-bar flex items-center justify-between h-10">

    </header>

     <!-- table -->
    <k-table
      :columns="columns"
      :options="options"
      :rows="rows"
      @cell="onCell"
      @header="onHeader"
    >
      <template #cell="{ columnIndex, value }">
        <p class="k-table-cell-value">
          <template v-if="columnIndex === 'path' || columnIndex === 'referrer'">
            <retour-table-link-preview :value="value" />
          </template>

          <template v-else>
            {{ value }}
          </template>
        </p>
      </template>
    </k-table>

    <!-- empty -->
    <div
      v-if="rows.length === 0"
      class="bg-white p-4 text-center rounded-sm shadow text-gray text-sm"
    >
      {{ $t("retour.tbl.fails.empty") }}
    </div>

    <!-- dialog -->
    <k-dialog
      ref="flushDialog"
      :submit-button="{
        text: $t('retour.settings.log.clear'),
        color: 'negative',
        icon: 'trash'
      }"
      @submit="onFlush"
    >
      <k-text>{{ $t('retour.settings.log.clear.confirm') }}</k-text>
    </k-dialog>
  </div>
</template>

<script>
import TableLinkPreview from "./TableLinkPreview.vue";

export default {
  components: {
    "retour-table-link-preview": TableLinkPreview
  },
  computed: {
    columns() {
      return {
        path: {
          label: this.$t("retour.fails.path"),
          width: "1/3"
        },
        referrer: {
          label: this.$t("retour.fails.referrer"),
          width: "1/3"
        },
        hits: {
          label: this.$t("retour.hits"),
          width: "1/12",
          align: "right"
        },
        last: {
          label: this.$t("retour.hits.last"),
          width: "3/12"
        }
      };
    },
    options() {
      return [
        { text: this.$t("retour.fails.resolve"), icon: "bolt", click: "add" }
      ];
    },
    rows() {
      // TODO: remove when fixed in core
      return this.$store.state.retour.data.failures.map(route => {
        Object.keys(route).forEach(key => {
          route[key] = route[key] || "";
        });
        return route;
      });
    }
  },
  methods: {
    onCell() {

    },
    async onFlush() {
      try {
        await this.$api.post("retour/logs/flush");
        this.$refs.flushDialog.close();
        this.$store.dispatch("retour/load");

      } catch (error) {
        this.$store.dispatch("notification/error", error);
      }
    },
    onHeader() {

    }
  }
}
</script>
