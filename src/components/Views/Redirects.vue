<template>
  <k-structure-field
    ref="field"
    name="retour"
    :columns="columns"
    :endpoints="endpoints"
    :fields="fields"
    :label="$t('retour.redirects')"
    :sortable="false"
    :value="values"
    :limit="options.limit"
    sort-by="status asc from asc"
    @input="update"
  />
</template>

<script>
export default {
  props: {
    options: Object
  },
  data() {
    return {
      redirects: []
    }
  },
  computed: {
    codes() {
      let codes = Object.keys(this.options.headers).map((code) => ({
        text:  code.substr(1) + " - " + this.options.headers[code],
        value: code.substr(1)
      }));

      codes.unshift({text: "-- disabled --", value: "disabled"});

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
          required: true
        },
        to: {
          label: this.$t("retour.redirects.to"),
          type: "text",
          help: this.$t("retour.redirects.to.help"),
        },
        status: {
          label: this.$t("retour.redirects.status"),
          type: "select",
          options: this.codes,
          width: "1/3",
          required: true,
          empty: false,
          default: "disabled"
        },
        stats: {
          label: this.$t("retour.redirects.hits"),
          type: "retour-stats",
          width: "2/3"
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
  created() {
    this.fetch();
  },
  methods: {
    fetch() {
      this.$store.dispatch("isLoading", true);
      this.$api.get("retour/redirects").then(response => {
        this.redirects = response;
        this.$store.dispatch("isLoading", false);
      });
    },
    update(input) {
      this.$store.dispatch("isLoading", true);
      this.$api.patch("retour/redirects", input.map(item => {
        delete(item["stats"]);
        delete(item["id"]);
        return item;
      })).then(() => {
        this.redirects = input;
        this.$store.dispatch("isLoading", false);
      });
    }
  }
}
</script>

<style>
.k-retour-view .k-field-name-from .k-text-input,
.k-retour-view .k-field-name-to   .k-text-input {
  padding-left: 2px;
}
</style>

