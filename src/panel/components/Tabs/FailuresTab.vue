<template>
  <k-view>
    <list
      name="failures"
      :columns="columns"
      :empty="$t('retour.failures.empty')"
      :options="options"
      :rows="data"
      @option="onOption"
    >
      <!-- button -->
      <template #button>
        <k-button
          icon="trash"
          @click="$dialog('retour/failures/flush')"
        >
          {{ $t('retour.failures.clear') }}
        </k-button>
      </template>
    </list>
  </k-view>
</template>

<script>
import List from "../List/List.vue";

export default {
  components: {
    List
  },
  props: {
    data: Object
  },
  computed: {
    columns() {
      return {
        path: {
          label: this.$t("retour.failures.path"),
          type: "link",
          filter: true,
          width: "1/2"
        },
        referrer: {
          label: this.$t("retour.failures.referrer"),
          type: "link",
          filter: true,
          width: "1/2"
        },
        hits: {
          label: this.$t("retour.hits"),
          type: "count",
          width: "1/12",
          align: "right"
        }
      };
    },
    options() {
      return [
        { text: this.$t("retour.failures.resolve"), icon: "add", click: "resolve" },
        { text: this.$t("remove"), icon: "trash", click: "remove" }
      ];
    }
  },
  methods: {
    onOption(option, row) {
      const path = encodeURIComponent(row.path);
      switch (option) {
      case "remove":
        return this.$dialog(`retour/failures/${path}/delete`);
      case "resolve":
        return this.$dialog(`retour/failures/${path}/resolve`);
      }
    }
  }
};
</script>
