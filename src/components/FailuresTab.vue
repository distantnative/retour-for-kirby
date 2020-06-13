<template>
  <retour-table
    :columns="columns"
    :empty="$t('retour.tbl.fails.empty')"
    :options="options"
    :rows="rows"
    tab="failures"
    @option="onResolve"
  >
    <template #dialogs>
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
    </template>

  </retour-table>
</template>

<script>
import Table from "./Table.vue";

export default {
  components: {
    "retour-table": Table
  },
  computed: {
    columns() {
      return {
        path: {
          label: this.$t("retour.fails.path"),
          type: "link",
          filter: true,
          width: "1/3"
        },
        referrer: {
          label: this.$t("retour.fails.referrer"),
          type: "link",
          filter: true,
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
        { text: this.$t("retour.fails.resolve"), icon: "add", click: "resolve" }
      ];
    },
    rows() {
      return this.$store.state.retour.data.failures;
    }
  },
  methods: {
    async onResolve(option, row) {
      await this.$router.push("#routes");
      this.$events.$emit("retour.resolve", { from: row.path });
    }
  }
}
</script>
