<template>
  <tbl
    :columns="columns"
    :rows="fails"
    :is-loading="this.$store.state.isLoading"
    v-bind="table"
    @action="action(...$event)"
  >

    <template slot="headline">
      <TableSwitch />
    </template>

    <template slot="column-recency" slot-scope="props">
      <p><recency :value="props.value" /></p>
    </template>

    <template slot="column-path" slot-scope="props">
      <p v-if="props.value !== '–'" class="k-url-field-preview">
        <k-link
          :to="site + '/' + props.value"
          target="_blank"
          @click.native.stop
        >
          {{ props.value }}
        </k-link>
      </p>
      <p v-else>
        {{ props.value }}
      </p>
    </template>
  </tbl>
</template>

<script>
import permissions from "../../mixins/permissions"
import date from "../../helpers/date.js";

import Recency from "../Fields/Recency.vue";
import TableSwitch from "./Switch.vue";
import Tbl from "tbl-for-kirby";

export default {
  mixins: [permissions],
  components: {
    Recency,
    TableSwitch,
    Tbl,
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
          label: this.$t("rt.fails.path"),
          field: "path",
          type: "url"
        },
        {
          label: this.$t("rt.fails.referrer"),
          field: "referrer",
          type: "url"
        },
        {
          label: this.$t("rt.hits"),
          field: "hits",
          type: "number",
          sort: "desc",
          search: false,
          width: "1/8"
        },
        {
          name: "last",
          label: this.$t("rt.hits.last"),
          field: "last",
          type: "date",
          sort: "desc",
          search: false,
          width: "1/6",
          responsive: false
        }
      ];
    },
    fails() {
      return date(this.$store.state.retour.data.fails);
    },
    site() {
      return window.panel.site;
    },
    table() {
      return {
        options: {
          reset: false
        },
        sort: {
          initialBy: "last"
        },
        actions: {
          inline: true,
          items: [
            { text: "Add as redirect", icon: "add", click: "add" }
          ]
        },
        labels: {
          all: this.$t("rt.tbl.all"),
          empty: this.$t("rt.tbl.fails.empty"),
          perPage: this.$t("rt.tbl.perPage"),
          filter: this.$t("rt.tbl.filter")
        }
      }
    }
  },
  methods: {
    action(action, row) {
      switch (action) {
        case "add":
          this.$store.dispatch("retour/table");
          this.$parent.$parent.$refs.redirects.action("add", {
            from: row.path
          }, "to", () => {
            this.$api.post("retour/resolve", {
              path: row.path
            }).then(() => {
              this.$store.dispatch("retour/fetchFails");
              this.$store.dispatch("retour/fetchStats");
            });
          });
          break;
      }
    }
  }
}
</script>
