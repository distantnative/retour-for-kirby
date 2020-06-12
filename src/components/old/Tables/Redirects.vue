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
        <k-button icon="cancel" class="rt-form-btn" @click="onCancel">{{ $t('cancel') }}</k-button>
        <k-button
          icon="check"
          class="rt-form-btn"
          @click="onSubmit"
          :disabled="!isValid"
        >{{ $t(mode === 'new' ? 'create' : 'change') }}</k-button>
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
      <k-text>{{ $t('field.structure.delete.confirm') }}</k-text>
    </k-dialog>
  </tbl>
</template>

<script>
import { date, status, permissions } from "../../lib/helpers.js";

import TableSwitch from "../Navigation/TableSwitch.vue";
import Tbl from "tbl-for-kirby";

export default {
  mixins: [permissions],
  components: {
    TableSwitch,
    Tbl
  },
  data() {
    return {
      mode: null,
      current: null,
      afterSubmit: null
    };
  },
  computed: {
    columns() {
      let columns = [
        {
          label: this.$t("retour.redirects.from"),
          type: "url",
          field: "from",
          width: "full"
        },
        {
          label: this.$t("retour.redirects.to"),
          type: "url",
          field: "to",
          width: "full",
          responsive: false
        },
        {
          label: this.$t("retour.redirects.status"),
          width: "1/4",
          field: "status"
        }
      ];

      if (this.hasLogs === true) {
        columns = [
          ...columns,
          {
            label: this.$t("retour.hits"),
            field: "hits",
            type: "number",
            sort: "desc",
            search: false,
            width: "1/6"
          },
          {
            name: "last",
            label: this.$t("retour.hits.last"),
            field: "last",
            type: "date",
            sort: "desc",
            search: false,
            width: "2/5",
            responsive: false
          }
        ];
      }

      return columns;
    },
    fields() {
      let fields = {
        from: {
          label: this.$t("retour.redirects.from"),
          type: "text",
          before: window.panel.site + "/",
          help: this.$t("retour.redirects.from.help", {
            docs: "https://distantnative.com/retour/docs"
          }),
          icon: "url",
          width: "1/2",
          counter: false,
          required: true
        },
        to: {
          label: this.$t("retour.redirects.to"),
          type: "rt-redirect",
          help: this.$t("retour.redirects.to.help"),
          icon: "retour",
          width: "1/2",
          counter: false
        },
        status: {
          label: this.$t("retour.redirects.status"),
          type: "rt-status",
          options: [
            { text: "––––", value: "–" },
            ...Object.keys(this.headers).map(code => ({
              text: code.substr(1) + " - " + this.headers[code],
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

      if (this.hasLogs === true) {
        fields = {
          ...fields,
          stats: {
            label: this.$t("retour.hits"),
            type: "info",
            text: `<b>${this.current.hits || 0} ${this.$t(
              "retour.hits"
            )}</b> (${this.$t("retour.hits.last")}: ${
              this.current.last
                ? this.$library
                    .dayjs(this.current.last)
                    .format("D MMM YYYY - HH:mm:ss")
                : "–"
            })`,
            width: "1/2"
          }
        };
      }

      return fields;
    },
    hasLogs() {
      return this.$store.state.retour.options.logs;
    },
    headers() {
      return this.$store.state.retour.options.headers;
    },
    isValid() {
      return (
        this.current.hasOwnProperty("from") &&
        this.current.from != "" &&
        this.current.hasOwnProperty("status") &&
        this.current.status != ""
      );
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
          all: this.$t("retour.tbl.all"),
          empty: this.$t("retour.tbl.redirects.empty"),
          perPage: this.$t("retour.tbl.perPage"),
          filter: this.$t("retour.tbl.filter")
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
          this.current.status = "–";
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
        updated.push(this.current);
      } else {
        updated[this.mode] = this.current;
      }

      this.update(updated).then(() => {
        // Mark potential fails as resolved
        if (this.mode === "new") {
          this.$api
            .post("retour/logs/resolve", {
              path: this.current.from
            })
            .then(() => {
              this.$store.dispatch("retour/fails");
              this.$store.dispatch("retour/stats");
            })
            .catch(error => {
              this.$store.dispatch("notification/error", error);
            });
        }

        this.onCancel();
      });
    },
    status: v => status(v),
    update(input) {
      return this.$api
        .patch("retour/redirects", input).then(() => {
          if (this.afterSubmit) {
            this.afterSubmit();
            this.afterSubmit = null;
          }

          this.$store.dispatch("retour/redirects");
        })
        .catch(error => {
          this.$store.dispatch("notification/error", error);
        });
    }
  }
};
</script>

<style lang="scss">
.rt-redirects-status {
  display: flex;

  code {
    background: rgba(0, 0, 0, 0.1);
    color: #16171a;
    border-radius: 3px;
    box-decoration-break: clone;
    font-family: var(--font-family-mono);
    font-size: var(--font-size-tiny);
    padding: 0.05em 0.5em;
    margin-left: 0.5em;
  }
}

.rt-form {
  padding: 0.5rem 0.75rem;
  background: #ddd;
  box-shadow: rgba(#16171a, 0.1) 0 0 0 3px;

  &-btn {
    color: #16171a;
  }
}
</style>
