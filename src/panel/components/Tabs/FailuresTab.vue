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
          width: "1/3"
        },
        referrer: {
          label: this.$t("retour.failures.referrer"),
          type: "link",
          filter: true,
          width: "1/3"
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
    onOption(option, row = {}, rowIndex = null) {
      switch (option) {
      case "remove":
        return this.$dialog("retour/failures/remove");
      case "resolve":
        return this.$dialog("retour/failures/resolve");
      }
    }
  }
};
</script>

<style>
.k-dialog[data-size="huge"] {
  width: 95vw;
  max-width: 50rem;
}
</style>
