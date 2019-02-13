<template>
  <k-structure-field
    ref="field"
    name="retour"
    :columns="columns"
    :disabled="!canUpdate"
    :endpoints="endpoints"
    :fields="fields"
    :label="$t('retour.redirects')"
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
          label: this.$t("retour.redirects.from"),
          width: "1/4",
          type: "url"
        },
        to: {
          label: this.$t("retour.redirects.to"),
          width: "1/4",
          type: "url"
        },
        status: {
          label: this.$t("retour.redirects.status"),
          width: "1/6",
          type: "retour-status"
        },
        stats: {
          label: this.$t("retour.redirects.hits"),
          type: "retour-count"
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
          label: this.$t("retour.redirects.from"),
          type: "text",
          before: this.options.site + "/",
          required: true,
          width: "1/2"
        },
        to: {
          label: this.$t("retour.redirects.to"),
          type: "text",
          width: "1/2"
        },
        status: {
          label: this.$t("retour.redirects.status"),
          type: "retour-status",
          options: this.codes,
          width: "1/2",
          required: true,
          empty: false,
          default: "disabled"
        },
        stats: {
          label: this.$t("retour.redirects.hits"),
          type: "retour-stats",
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

