<script>
import CollectionView from "./CollectionView.vue";

export default {
  extends: CollectionView,
  data() {
    return {
      sortBy: "hits",
      sortDirection: "desc",
    };
  },
  computed: {
    buttons() {
      return [
        {
          icon: "trash",
          text: this.$t("retour.failures.clear"),
          click: () => this.$dialog("retour/failures/flush"),
        },
      ];
    },
    columns() {
      return {
        path: {
          label: this.$t("retour.failures.path"),
          type: "path",
          width: "1/3",
        },
        referrer: {
          label: this.$t("retour.failures.referrer"),
          type: "path",
          width: "1/3",
        },
        last: {
          label: this.$t("retour.last"),
          type: "date",
          display: "D MMM YYYY, HH:mm:ss",
          width: "2/8",
        },
        hits: {
          label: this.$t("retour.hits"),
          type: "count",
          width: "1/16",
          align: "right",
        },
      };
    },
    empty() {
      return {
        icon: "cloud-off",
        text: this.$t("retour.failures.empty"),
      };
    },
  },
  methods: {
    options(failure) {
      return [
        {
          text: this.$t("retour.failures.resolve"),
          icon: "add",
          click: () =>
            this.$drawer(`retour/failures/${this.id(failure.path)}/resolve`),
        },
        {
          text: this.$t("remove"),
          icon: "trash",
          click: () =>
            this.$dialog(`retour/failures/${this.id(failure.path)}/delete`),
        },
      ];
    },
  },
};
</script>
