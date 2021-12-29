<template>
  <k-view>
    <list
      name="redirects"
      :columns="columns"
      :empty="$t('retour.redirects.empty')"
      :options="options"
      :rows="data"
      @cell="onOption('edit', $event.row, $event.rowIndex, $event.columnIndex)"
      @option="onOption"
    >
      <!-- button -->
      <template #button>
        <k-button icon="add" @click="onOption('create')">
          {{ $t("add") }}
        </k-button>
      </template>
    </list>
  </k-view>
</template>

<script>
import List from "../List/List.vue";

export default {
  components: {
    List,
  },
  props: {
    data: Object,
    hasLog: Boolean,
  },
  computed: {
    columns() {
      let columns = {
        from: {
          label: this.$t("retour.redirects.from"),
          type: "link",
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
          width: "1/20",
        },
      };

      if (this.hasLog) {
        columns.hits = {
          label: this.$t("retour.hits"),
          width: "1/10",
          type: "count",
        };
      }

      return columns;
    },
    options() {
      return [
        { text: this.$t("edit"), icon: "edit", click: "edit" },
        { text: this.$t("remove"), icon: "trash", click: "delete" },
      ];
    },
  },
  methods: {
    // eslint-disable-next-line no-unused-vars
    onOption(option, row = {}, rowIndex = null, column = null) {
      if (option === "create") {
        return this.$dialog("retour/redirects/create");
      }

      const id = encodeURIComponent(row.from);
      return this.$dialog(`retour/redirects/${id}/${option}`, {
        query: { column },
      });
    },
  },
};
</script>
