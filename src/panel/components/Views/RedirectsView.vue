<script>
import CollectionView from "./CollectionView.vue";

export default {
  extends: CollectionView,
  data() {
    return {
      sortBy: "from",
    };
  },
  computed: {
    buttons() {
      return [
        {
          icon: "add",
          text: this.$t("add"),
          click: () => this.$drawer("retour/redirects/create"),
        },
      ];
    },
    columns() {
      let columns = {
        from: {
          label: this.$t("retour.redirects.from"),
          type: "path",
          width: "7/20",
        },
        to: {
          label: this.$t("retour.redirects.to"),
          type: "link",
          width: "7/20",
        },
        status: {
          label: this.$t("retour.redirects.status"),
          type: "status",
          width: "1/10",
        },
        priority: {
          label: this.$t("retour.redirects.priority.abbr"),
          type: "priority",
          width: "1/10",
        },
      };

      if (this.stats) {
        columns.hits = {
          label: this.$t("retour.hits"),
          width: "1/10",
          type: "count",
        };
      }

      return columns;
    },
    empty() {
      return {
        icon: "shuffle",
        text: this.$t("retour.redirects.empty"),
      };
    },
  },
  methods: {
    onCell({ row, columnIndex }) {
      this.$drawer(`retour/redirects/${this.id(row.from)}/edit`, {
        query: {
          column: columnIndex,
        },
      });
    },
    options(redirect) {
      return [
        {
          text: this.$t("edit"),
          icon: "edit",
          click: () =>
            this.$drawer(`retour/redirects/${this.id(redirect.from)}/edit`),
        },
        {
          text: this.$t("remove"),
          icon: "trash",
          click: () =>
            this.$dialog(`retour/redirects/${this.id(redirect.from)}/delete`),
        },
      ];
    },
  },
};
</script>
