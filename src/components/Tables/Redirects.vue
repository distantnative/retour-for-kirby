<template>
  <tbl
    :columns="columns"
    :rows="redirects"
    :is-loading="$store.state.isLoading"
    v-bind="table"
    @add="action('add')"
    @action="action(...$event)"
  >

    <!-- Custom headline: table switcher -->
    <template slot="headline">
      <TableSwitch />
    </template>

    <!-- Custom field cells -->
    <template
      v-if="hasLogs"
      slot="column-recency"
      slot-scope="props"
    >
      <p><recency :value="props.value" /></p>
    </template>

    <template slot="column-status" slot-scope="props">
      <p class="rt-redirects-status" :data-status="status(props.value)">
        <k-icon type="circle" />
        <code>{{ props.value }}</code>
      </p>
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

    <!-- Replace parts of k-tbl for add/edit screen -->
    <template v-if="mode !== null">
      <div slot="filter" />

      <k-form
        ref="form"
        slot="table"
        v-model="current"
        :fields="fields"
        class="rt-form"
        @submit="onSubmit"
      />

      <template slot="footer">
        <k-button
          icon="cancel"
          class="rt-form-btn"
          @click="onCancel"
        >
          {{ $t('cancel') }}
        </k-button>
        <k-button
          icon="check"
          class="rt-form-btn"
          @click="onSubmit"
        >
          {{ $t(mode === 'new' ? 'create' : 'change') }}
        </k-button>
      </template>
    </template>

    <!-- Dialogs -->
    <k-dialog
      slot="dialogs"
      ref="remove"
      :button="$t('delete')"
      theme="negative"
      icon="trash"
      @cancel="onCancel"
      @submit="onRemove"
    >
      <k-text>
        {{ $t('field.structure.delete.confirm') }}
      </k-text>
    </k-dialog>
  </tbl>
</template>

<script>
import {date, status, permissions} from "../helpers.js";

import Recency from "../Fields/Recency.vue";
import TableSwitch from "./Switch.vue";
import Tbl from "tbl-for-kirby";

export default {
  mixins: [permissions],
  components: {
    Recency,
    TableSwitch,
    Tbl
  },
  data() {
    return {
      mode: null,
      current: null,
      afterSubmit: null
    }
  },
  computed: {
    columns() {
      let columns = [
        {
          label: this.$t("rt.redirects.from"),
          type: "url",
          field: "from"
        },
        {
          label: this.$t("rt.redirects.to"),
          type: "url",
          field: "to",
          responsive: false
        },
        {
          label: this.$t("rt.redirects.status"),
          width: "1/6",
          field: "status"
        }
      ];

      if (this.hasLogs === true) {
        columns = [
          {
            name: "recency",
            field: "last",
            sort: false,
            search: false,
            width: "1fr",
            responsive: false,
          },
          ...columns,
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
      }

      return columns;
    },
    fields() {
      let fields = {
        from: {
          label: this.$t("rt.redirects.from"),
          type: "text",
          before: window.panel.site + "/",
          help: this.$t("rt.redirects.from.help", {
            reference: "https://getkirby.com/docs/guide/routing#patterns",
            readme: "https://github.com/distantnative/retour-for-kirby#redirects",
          }),
          icon: "url",
          width: "1/2",
          counter: false,
          required: true
        },
        to: {
          label: this.$t("rt.redirects.to"),
          type: "rt-redirect",
          help: this.$t("rt.redirects.to.help"),
          icon: "retour",
          width: "1/2",
          counter: false,
        },
        status: {
          label: this.$t("rt.redirects.status"),
          type: "rt-status",
          options: [
            { text: "––––", value: "disabled" },
            ...Object.keys(this.headers).map((code) => ({
              text:  code.substr(1) + " - " + this.headers[code],
              value: code.substr(1)
            }))
          ],
          help: this.$t("rt.redirects.status.help", { url: "https://httpstatuses.com" }),
          empty: false,
          required: true,
          width: "1/2"
        }
      }

      if (this.hasLogs === true) {
        fields = {
          ...fields,
          stats: {
            label: this.$t("rt.hits"),
            type: "info",
            text: `<b>${this.current.hits || 0} ${this.$t("rt.hits")}</b> (${this.$t("rt.hits.last")}: ${this.current.last || "–"})`,
            width: "1/2"
          },
        }
      }

      return fields;
    },
    hasLogs() {
      return this.$store.state.retour.options.logs;
    },
    headers() {
      return this.$store.state.retour.options.headers;
    },
    redirects() {
      return date(this.$store.state.retour.data.redirects);
    },
    site() {
      return window.panel.site;
    },
    table() {
      let config = {
        options: {
          add: this.canUpdate,
          reset: false
        },
        sort: {
          initialBy: "status"
        },
        labels: {
          all: this.$t("rt.tbl.all"),
          empty: this.$t("rt.tbl.redirects.empty"),
          perPage: this.$t("rt.tbl.perPage"),
          filter: this.$t("rt.tbl.filter")
        }
      };

      if (this.canUpdate) {
        config.actions = {
          items: [
            { text: this.$t("edit"), icon: "edit", click: "edit" },
            { text: this.$t("remove"), icon: "trash", click: "remove" }
          ],
          onRow: "edit"
        };
      }

      return config;
    }
  },
  methods: {
    action(action, row = {}, field, callback) {
      this.current = Object.assign({}, row);

      switch (action) {
        case "add":
          this.mode = "new";
          this.current.status = "disabled";
          this.afterSubmit = callback;
          this.$nextTick(() => this.$refs.form.focus("from"));
          break;

        case "edit":
          this.mode = this.redirects.findIndex(x => x.from === row.from);
          this.$nextTick(() => this.$refs.form.focus(field || "from"));
          break;

        case "remove":
          this.$refs.remove.open();
          break;
      }
    },
    onCancel() {
      this.mode = null;
      this.current = null;
      this.afterSubmit = null;
    },
    onPrev() {
      this.mode -= 1;
      this.current = this.redirects[this.mode];
    },
    onNext() {
      this.mode += 1;
      this.current = this.redirects[this.mode];
    },
    onRemove() {
      this.update(this.redirects.filter(x => x.from !== this.current.from));
      this.$refs.remove.close();
      this.current = null;
    },
    onSubmit() {
      let updated = this.redirects;

      if (this.mode === "new") {
        this.$api.post("retour/resolve", {
          path: this.current.from
        }).then(() => {
          this.$store.dispatch("retour/fetchFails");
          this.$store.dispatch("retour/fetchStats");
        });

        updated.push(this.current);

      } else {
        updated[this.mode] = this.current;
      }

      this.update(updated).then(this.onCancel);
    },
    status: (v) => status(v),
    update(input) {
      return this.$api.patch("retour/redirects", input).then(() => {

        if (this.afterSubmit) {
          this.afterSubmit();
          this.afterSubmit = null;
        }

        this.$store.dispatch("retour/fetchRedirects");
      });
    }
  }
}
</script>

<style lang="scss">
.rt-redirects-status {
  display: flex;

  code {
    background: rgba(0,0,0,.1);
    color: #16171a;
    border-radius: 3px;
    box-decoration-break: clone;
    font-family: SFMono-Regular,Consolas,Liberation Mono,Menlo,Courier,monospace;
    font-size: .875em;
    padding: .05em .5em;
    margin-left: .5em;
  }
}

.rt-form {
  padding: .5rem .75rem;
  background: #ddd;
  box-shadow: rgba(#16171a, 0.1) 0 0 0 3px;

  &-btn {
    color: #16171a;
  }
}
</style>
