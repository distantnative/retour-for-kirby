<template>
  <k-inside class="k-retour-view">
    <template #topbar>
      <k-retour-timespan :timespan="timespan" />
    </template>

    <k-retour-stats v-if="stats" :data="stats" :timespan="timespan" />

    <k-retour-tabs :tab="tab" :tabs="tabs">
      <template #buttons>
        <k-button-group :buttons="buttons" size="sm" variant="filled" />
      </template>
    </k-retour-tabs>

    <k-collection
      :columns="columns"
      :empty="empty"
      :items="items"
      :pagination="{ ...pagination, total: data.length }"
      layout="table"
      @paginate="pagination.page = $event.page"
    >
      <template #options="{ item }">
        <k-options-dropdown :options="options(item)" />
      </template>
    </k-collection>
  </k-inside>
</template>

<script>
export default {
  props: {
    data: [Object, Array],
    stats: [Boolean, Array],
    tab: String,
    tabs: Array,
    timespan: Object,
  },
  data() {
    return {
      pagination: {
        page: 1,
        limit: 50,
      },
    };
  },
  computed: {
    buttons() {
      return [];
    },
    columns() {
      return {};
    },
    empty() {
      return {};
    },
    items() {
      return this.data.slice(
        this.pagination.limit * (this.pagination.page - 1),
        this.pagination.limit * this.pagination.page
      );
    },
  },
  methods: {
    options(item) {
      return [];
    },
    id(path) {
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
       * Many hosting solutions do not allow customers to change such
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
      return encodeURIComponent(path.replace(/\//g, "\x1D"));
    },
  },
};
</script>
