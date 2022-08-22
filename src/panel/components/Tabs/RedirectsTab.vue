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
          type: "path",
          width: "7/20",
        },
        to: {
          label: this.$t("retour.redirects.to"),
          type: "path",
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

      /**
       * Fix for issue #300 (See https://github.com/distantnative/retour-for-kirby/issues/300):
       *
       * Depending on the settings, the webserver might not always handle
       * escaped forward-slashes in the way, which this plugin expects it to.
       *
       * This specifically results in a 404 when trying to edit a redirect-entry,
       * unless the ```AllowEncodedSlashes NoDecode``` is set for the Apache
       * Server. The problem occurs in relation to nginx.
       *
       * Many hosting solutions does now allow customers to change such
       * settings for the web-server, and so another solution is required.
       *
       * So, in order to remedy this problem, we replace forward-slash with the
       * non-visible ascii-characer "GROUP-SEPARATOR" (Oct: 035, Dec: 29,
       * Hex: 1D). By using a non-visible chracter we ensure that the id
       * generation from redirect pattern is always unique.
       *
       * Note that this fix include changes to two parts of the plug-in
       * code-base. In this file, and in src/classes/Redirect.php
       */
      const id = encodeURIComponent(row.from.replace(/\//g, "\x1D"));
      return this.$dialog(`retour/redirects/${id}/${option}`, {
        query: { column },
      });
    },
  },
};
</script>
