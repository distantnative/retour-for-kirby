<template>
  <tbl
    :headline="`${this.$t('rt.redirects')} (${this.redirects.length})`"
    :columns="columns"
    :rows="redirects"
    :isLoading="this.$store.state.isLoading"
    v-bind="table"
    @add="action('add')"
    @action="action(...$event)"
  >
    <!-- Custom field cells -->
    <template slot="column-health" slot-scope="props">
      <p class="rt-redirects-health">
        <k-icon
          :type="health[props.index].type"
          :title="health[props.index].tooltip"
          :data-health="health[props.index].type"
        />
      </p>
    </template>

    <template slot="column-status" slot-scope="props">
      <p class="rt-redirects-status" :data-status="status(props.value)">
        <k-icon type="circle" />
        <code>{{ props.value }}</code>
      </p>
    </template>

    <template slot="column-stats" slot-scope="props">
      <k-rt-count-field-preview :value="{
          hits: props.row.hits,
          last: props.row.last
        }" />
    </template>

    <!-- Replace parts of k-tbl for add/edit screen -->
    <template v-if="mode !== null">
      <div slot="filter" />

      <k-form
        ref="form"
        slot="table"
        :fields="fields"
        v-model="current"
        class="rt-form"
        @submit="submit"
      />

      <template slot="footer">
          <k-button
            icon="cancel"
            class="rt-form-btn"
            @click="cancel"
          >
            {{ $t('cancel') }}
          </k-button>
          <k-button
            icon="check"
            class="rt-form-btn"
            @click="submit"
          >
            {{ $t(mode === 'new' ? 'create' : 'change') }}
          </k-button>
      </template>
    </template>

    <!-- Dialogs -->
    <template slot="dialogs">
      <k-dialog
        ref="remove"
        :button="$t('delete')"
        theme="negative"
        icon="trash"
        @cancel="cancel"
        @submit="remove"
      >
        <k-text>
          {{ $t('field.structure.delete.confirm') }}
        </k-text>
      </k-dialog>
    </template>

    <template slot="footer-before-perPage">
      <k-button
        icon="search"
        class="k-tbl-reset"
        @click="checkHealth"
      >
        {{ $t("rt.redirects.health.btn") }}
      </k-button>
    </template>

  </tbl>
</template>

<script>
import Tbl from "tbl-for-kirby";
import status from "../../assets/status.js";

export default {
  components: { Tbl },
  props: {
    canUpdate: Boolean,
    redirects: Array,
    options: Object,
  },
  data() {
    return {
      mode: null,
      current: null,
      health: null
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
        },
        {
          label: this.$t("rt.redirects.hits"),
          field: "stats",
          width: "1/6",
          search: false,
          responsive: false
        }
      ];

      if (this.health) {
        columns.unshift({
          name: "health",
          width: "1fr"
        });
      }

      return columns;
    },
    fields() {
      return {
        from: {
          label: this.$t("rt.redirects.from"),
          type: "text",
          before: this.options.site + "/",
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
          type: "text",
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
            ...Object.keys(this.options.headers).map((code) => ({
              text:  code.substr(1) + " - " + this.options.headers[code],
              value: code.substr(1)
            }))
          ],
          help: this.$t("rt.redirects.status.help", { url: "https://httpstatuses.com" }),
          empty: false,
          required: true,
          width: "1/2"
        },
        stats: {
          label: this.$t("rt.redirects.hits"),
          type: "info",
          text: `<b>${this.current.hits || 0} ${this.$t("rt.redirects.hits")}</b> (${this.$t("rt.redirects.hit.last")}: ${this.current.last || "–"})`,
          width: "1/2"
        },
      }
    },
    table() {
      return {
        options: {
          add: true
        },
        sort: {
          initialBy: "status"
        },
        actions: {
          items: [
            { text: this.$t("edit"), icon: "edit", click: "edit" },
            { text: this.$t("remove"), icon: "remove", click: "remove" }
          ],
          onRow: 'edit'
        }
      }
    }
  },
  methods: {
    action(action, row = {}, field) {
      this.current = Object.assign({}, row);

      switch (action) {
        case "add":
          this.mode = "new";
          this.current.status = "disabled";
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
    cancel() {
      this.mode = null;
      this.current = null;
    },
    checkHealth() {
      this.health = this.redirects.map(x => "loader");

      const promises = this.redirects.map(async obj => {
        let placeholders = [];
        const from = obj.from.replace(/\(\:.*?\??\)/g, (match, $1) => {
          if (match === "(:num)" || match === "(:num?)") {
            placeholders.push(2019);
            return 2019;
          }

          placeholders.push("kirby");
          return "kirby";
        });

        return fetch(this.options.site + "/" + from)
          .then(response => {
            if (obj.status === "disabled") {
              return {
                type: "protected",
                tooltip: this.$t("rt.redirects.health.disabled")
              };
            }

            if (response.status === parseInt(obj.status)) {
              return {
                type: "smile",
                tooltip: this.$t("rt.redirects.health.good")
              };
            }

            if (obj.to) {
              let to = obj.to;
              placeholders.forEach((placeholder, index) => {
                to = to.replace("$" + (index + 1), placeholder);
              });

              if (to.startsWith("http")) {
                if (response.url === to && response.ok) {
                  return {
                    type: "smile",
                    tooltip: this.$t("rt.redirects.health.good")
                  };
                }
              } else {
                if (response.url === this.options.site + "/" + to && response.ok) {
                  return {
                    type: "smile",
                    tooltip: this.$t("rt.redirects.health.good")
                  };
                }
              }
            }

            return {
              type: "alert",
              tooltip: this.$t("rt.redirects.health.bad")
            };
          });
      });

      Promise.all(promises).then(completed => {
        this.health = completed;
      });
    },
    prev() {
      this.mode -= 1;
      this.current = this.redirects[this.mode];
    },
    next() {
      this.mode += 1;
      this.current = this.redirects[this.mode];
    },
    remove() {
      this.update(this.redirects.filter(x => x.from !== this.current.from));
      this.$refs.remove.close();
      this.current = null;
    },
    status: (v) => status(v),
    submit() {
      if (this.mode === "new") {
        this.redirects = [...this.redirects, this.current];
      } else {
        this.redirects[this.mode] = this.current;
      }
      this.update(this.redirects).then(this.cancel);
    },
    update(input) {
      return this.$api.patch("retour/redirects", input).then(() => {
        this.$emit("update");
      });
    }
  }
}
</script>

<style lang="scss">
.rt-redirects-health {
  color: #ddd;

  [data-health="alert"] {
    color: #c82829;
  }
}

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
