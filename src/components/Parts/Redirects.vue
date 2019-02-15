<template>
  <k-structure-field
    ref="field"
    name="retour"
    :columns="columns"
    :disabled="!canUpdate"
    :endpoints="endpoints"
    :fields="fields"
    :label="$t('rt.redirects')"
    :sortable="false"
    :value="values"
    :limit="options.limit"
    sort-by="status asc from asc hits desc"
    @input="update"
  />
</template>

<script>
export default {
  props: {
    canUpdate: Boolean,
    redirects: Array,
    options: Object,
  },
  computed: {
    codes() {
      let codes = Object.keys(this.options.headers).map((code) => ({
        text:  code.substr(1) + " - " + this.options.headers[code],
        value: code.substr(1)
      }));

      codes.unshift({text: "––––", value: "disabled"});

      return codes;
    },
    columns() {
      return {
        from: {
          label: this.$t("rt.redirects.from"),
          width: "1/4",
          type: "url"
        },
        to: {
          label: this.$t("rt.redirects.to"),
          width: "1/4",
          type: "url"
        },
        status: {
          label: this.$t("rt.redirects.status"),
          width: "1/6",
          type: "rt-status"
        },
        stats: {
          label: this.$t("rt.redirects.hits"),
          type: "rt-count"
        }
      }
    },
    endpoints() {
      return {
        field: "retour",
        section: null,
        model: null
      };
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
          required: true,
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
          options: this.codes,
          help: this.$t("rt.redirects.status.help", { url: "https://httpstatuses.com" }),
          empty: false,
          required: true,
          default: "disabled",
          width: "1/2",
        },
        stats: {
          label: this.$t("rt.redirects.hits"),
          type: "rt-count",
          width: "1/2"
        },
      }
    },
    values() {
      return this.redirects.map(value => ({
        ...value,
        stats: {
          hits: value.hits,
          last: value.last
        }
      }));
    }
  },
  methods: {
    update(input) {
      this.$api.patch("retour/redirects", input.map(item => {
        delete(item["stats"]);
        delete(item["id"]);
        return item;
      })).then(() => {
        this.$emit("update", input);
      });
    }
  }
}
</script>

<style>
.k-retour-view .k-field-name-from .k-text-input {
  padding-left: 2px;
}

.k-retour-view .k-structure-field .k-field-header .k-button {
  padding: 1rem 0;
  line-height: 1rem;
}
</style>
