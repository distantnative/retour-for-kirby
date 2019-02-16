<template>
  <k-tbl
    :headline="`${$t('rt.fails')} (${logs.length})`"
    :columns="columns"
    :rows="logs"
    :options="table"
    :actions="actions"
    :isLoading="this.$store.state.isLoading"
    @action="action(...$event)"
  >
    <template slot="field-recency" slot-scope="props">
      <p><recency :value="props.value" /></p>
    </template>
  </k-tbl>
</template>

<script>
import Recency from "../Misc/Recency.vue";

export default {
  components: { Recency },
  props: {
    canUpdate: Boolean,
    logs: Array,
    options: Object,
  },
  computed: {
    actions() {
      return [
        { text: "Add as redirect", icon: "add", click: "add" }
      ]
    },
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
          width: "1/8",
          align: "right"
        },
        {
          label: this.$t('rt.redirects.state'),
          field: "redirected",
          type: "number",
          sort: "desc",
          search: false,
          width: "1/8",
          align: "right",
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
          align: "right",
          responsive: false
        }
      ];
    },
    table() {
      return {
        inlineActions: true,
        initialSort: "last",
      };
    }
  },
  methods: {
    action(action, row) {
      switch (action) {
        case "add":
          this.$emit("go", ["redirects", (view) => {
            view.$refs.redirects.action("add", { from: row.path });
          }]);
          break;
      }
    }
  }
}
</script>
