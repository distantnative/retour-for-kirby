<template>
  <tbl
    :headline="`${$t('rt.fails')} (${logs.length})`"
    :columns="columns"
    :rows="logs"
    :isLoading="this.$store.state.isLoading"
    v-bind="table"
    @action="action(...$event)"
  >
    <template slot="column-recency" slot-scope="props">
      <p><recency :value="props.value" /></p>
    </template>
  </tbl>
</template>

<script>
import Tbl from "tbl-for-kirby";
import Recency from "../Misc/Recency.vue";

export default {
  components: { Tbl, Recency },
  props: {
    canUpdate: Boolean,
    logs: Array,
    options: Object,
  },
  computed: {
    columns() {
      return [
        {
          name: "recency",
          field: "last",
          sort: false,
          search: false,
          width: "1fr",
          responsive: false,
        },
        {
          label: this.$t('rt.fails.path'),
          field: 'path',
          type: "url"
        },
        {
          label: this.$t('rt.fails.referrer'),
          field: 'referrer',
          type: "url"
        },
        {
          label: this.$t('rt.fails.state'),
          field: "failed",
          type: "number",
          sort: "desc",
          search: false,
          width: "1/8"
        },
        {
          label: this.$t('rt.redirects.state'),
          field: "redirected",
          type: "number",
          sort: "desc",
          search: false,
          width: "1/8",
          responsive: false
        },
        {
          name: "last",
          label: this.$t('rt.fails.last'),
          field: "last",
          type: "date",
          sort: "desc",
          search: false,
          width: "1/6",
          responsive: false
        }
      ];
    },
    table() {
      return {
        sort: {
          initialBy: "last"
        },
        actions: {
          inline: true,
          items: [
            { text: "Add as redirect", icon: "add", click: "add" }
          ]
        }
      }
    }
  },
  methods: {
    action(action, row) {
      switch (action) {
        case "add":
          this.$emit("go", ["redirects", (view) => {
            view.$refs.redirects.action("add", { from: row.path }, "to");
          }]);
          break;
      }
    }
  }
}
</script>
