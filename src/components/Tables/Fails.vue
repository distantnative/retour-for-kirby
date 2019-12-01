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

    <template slot="column-$default" slot-scope="props">
      <p>
        <k-button
          v-if="props.column.type === 'url' && props.value && props.value !== '–'"
          :link="(props.value && props.value.startsWith('http')) ? props.value : site + '/' + props.value"
          icon="url"
          target="_blank"
          @click.native.stop
        />
        {{ props.value }}
      </p>
    </template>
  </tbl>
</template>

<script>
import {date, permissions} from "../helpers.js";

import TableSwitch from "../Navigation/TableSwitch.vue";
import Tbl from "tbl-for-kirby";

export default {
  mixins: [permissions],
  components: {
    TableSwitch,
    Tbl,
  },
  computed: {
    columns() {
      return [
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
      let config = {
        options: {
          reset: false
        },
        sort: {
          initialBy: "last"
        },
        labels: {
          all: this.$t("rt.tbl.all"),
          empty: this.$t("rt.tbl.fails.empty"),
          perPage: this.$t("rt.tbl.perPage"),
          filter: this.$t("rt.tbl.filter")
        }
      };

      if (this.canUpdate) {
        config.actions = {
          inline: true,
          items: [
            { text: this.$t("rt.fails.resolve"), icon: "add", click: "add" }
          ]
        };
      }

      return config;
    }
  },
  methods: {
    action(action, row) {
      switch (action) {
        case "add":
          this.$store.dispatch("retour/table", "redirects");
          this.$parent.$parent.$refs.redirects.action("add", {
            from: row.path
          }, "to");
          break;
      }
    }
  }
}
</script>
