<template>
  <k-inside class="k-retour-view k-retour-collection-view">
    <template v-if="stats" #topbar>
      <k-retour-timespan :timespan="timespan" />
    </template>

    <!-- Stats -->
    <k-retour-stats v-if="stats" :data="stats" :timespan="timespan" />

    <!-- Buttons in task bar -->
    <k-retour-tabs
      :tab="tab"
      :tabs="tabs"
      :buttons="[{ icon: 'filter', click: toggleSearch }, ...buttons]"
    />

    <!-- Search filter  -->
    <k-input
      v-if="searching"
      :autofocus="true"
      :placeholder="$t('search') + ' …'"
      :value="q"
      type="text"
      class="k-models-section-search"
      @input="
        q = $event;
        pagination.page = 1;
      "
      @keydown.esc="toggleSearch"
    />

    <!-- Empty state -->
    <k-empty v-if="filteredItems.length === 0" v-bind="empty" layout="table" />

    <!-- Table -->
    <k-table
      v-else
      :columns="columns"
      :index="index"
      :rows="paginatedItems"
      @cell="onCell"
      @header="onHeader"
    >
      <template #header="{ columnIndex, label }">
        <span>
          {{ label }}
          <k-icon
            v-if="columnIndex === sortBy"
            :type="sortDirection === 'asc' ? 'angle-up' : 'angle-down'"
          />
        </span>
      </template>
      <template #options="{ row }">
        <k-options-dropdown :options="options(row)" />
      </template>
    </k-table>

    <footer class="k-bar k-collection-footer">
      <!-- Donate text -->
      <k-text class="k-help" v-html="help" />

      <k-pagination
        v-bind="pagination"
        :details="true"
        :total="filteredItems.length"
        @paginate="pagination.page = $event.page"
      />
    </footer>
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
      searching: false,
      q: null,
      sortDirection: "asc",
      pagination: {
        page: 1,
        limit: 20,
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
    filteredItems() {
      let items = this.data;

      if (this.q) {
        items = items.filter((item) =>
          JSON.stringify(item).toLowerCase().includes(this.q.toLowerCase())
        );
      }

      return items;
    },
    help() {
      if (this.stats) {
        return;
      }

      return `${this.$t(
        "retour.system.support"
      )}: 💛 <a href='https://paypal.me/distantnative'><strong> ${this.$t(
        "retour.system.support.donate"
      )}</strong></a>`;
    },
    index() {
      return (this.pagination.page - 1) * this.pagination.limit + 1;
    },
    paginatedItems() {
      return this.filteredItems
        .sortBy(`${this.sortBy} ${this.sortDirection}`)
        .slice(this.index - 1, this.pagination.limit * this.pagination.page);
    },
  },
  methods: {
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
    onCell({ row, columnIndex }) {},
    onHeader({ column, columnIndex }) {
      if (this.sortBy === columnIndex) {
        this.sortDirection = this.sortDirection === "asc" ? "desc" : "asc";
      } else {
        this.sortDirection = "asc";
      }

      this.sortBy = columnIndex;
      this.pagination.page = 1;
    },
    options(item) {
      return [];
    },
    toggleSearch() {
      this.searching = !this.searching;
      this.q = null;
    },
  },
};
</script>

<style>
.k-retour-collection-view .k-table-column {
  cursor: pointer;
}
.k-retour-collection-view .k-table-column span {
  display: inline-flex;
  width: 100%;
  align-items: center;
  justify-content: space-between;
}
</style>
