<template>
  <k-view>
    <list
      name="redirects"
      :columns="columns"
      :empty="$t('retour.redirects.empty')"
      :options="options"
      :rows="data"
      @cell="onOption('edit', ...$event)"
      @option="onOption"
    >
      <!-- button -->
      <template #button>
        <k-button
          icon="add"
          @click="onOption('add')"
        >
          {{ $t('add') }}
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
    data: Object,
    hasLog: Boolean
  },
  computed: {
    columns() {
      let columns =  {
        from: {
          label: this.$t("retour.redirects.from"),
          type: "link",
          filter: true,
          width: "7/20"
        },
        to: {
          label: this.$t("retour.redirects.to"),
          type: "link",
          filter: true,
          width: "7/20"
        },
        status: {
          label: this.$t("retour.redirects.status"),
          type: "status",
          width: "1/10"
        },
        priority: {
          label: this.$t("retour.redirects.priority.abbr"),
          type: "priority",
          width: "1/20"
        }
      };

      if (this.hasLog) {
        columns.hits = {
          label: this.$t("retour.hits"),
          width: "1/10",
          type: "count"
        };
      }

      return columns;
    },
    options() {
      return [
        { text: this.$t("edit"), icon: "edit", click: "edit" },
        { text: this.$t("remove"), icon: "trash", click: "remove" }
      ];
    }
  },
  methods: {
    onOption(option, row = {}, rowIndex = null) {
      switch (option) {
      case "add":
        return this.$dialog("retour/redirects/create");
      case "edit":
        return this.$dialog(`retour/redirects/${rowIndex}/edit`);
      case "remove":
        return this.$dialog(`retour/redirects/${rowIndex}/delete`);
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
