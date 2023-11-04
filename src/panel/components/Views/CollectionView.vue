<template>
  <k-retour-view v-bind="$props" class="k-retour-collection-view">
    <template #buttons>
      <k-button
       v-if="!stats"
       link="https://paypal.me/distantnative"
       theme="positive"
       icon="heart"
       size="sm"
       variant="filled"
      >
        {{  $t('retour.system.support') }}
      </k-button>

      <k-search-input
        v-if="searching"
        ref="search"
        :autofocus="true"
        :placeholder="$t('filter') + ' …'"
        :value="q"
        class="k-models-section-search k-input"
        @input="
          q = $event;
          pagination.page = 1;
        "
        @keydown.native.esc="toggleSearch(true)"
      />

      <k-button icon="filter"
        :title="$t('filter')"
        size="sm"
        variant="filled"
        @click="toggleSearch"
      />
      <k-button
        v-for="(button, index) in buttons"
        v-bind="button"
        :key="index"
        size="sm"
        variant="filled"
      />
    </template>

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
      <k-pagination
        v-bind="pagination"
        :details="true"
        :total="filteredItems.length"
        @paginate="pagination.page = $event.page"
      />
    </footer>
  </k-retour-view>
</template>

<script>
import { props as View } from "./View.vue";

export default {
  mixins: [View],
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
          JSON.stringify(item).toLowerCase().includes(this.q.toLowerCase()),
        );
      }

      return items;
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
    onCell() {},
    onHeader({ columnIndex }) {
      if (this.sortBy === columnIndex) {
        this.sortDirection = this.sortDirection === "asc" ? "desc" : "asc";
      } else {
        this.sortDirection = "asc";
      }

      this.sortBy = columnIndex;
      this.pagination.page = 1;
    },
    options() {
      return [];
    },
    async toggleSearch(forgiving = false) {
      if (forgiving && this.q) {
        this.q = null;
        return;

      }

      this.q = null;
      this.searching = !this.searching;

      if (this.searching) {
        await this.$nextTick();
        this.$refs.search.focus();
      }
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
.k-retour-collection-view .k-models-section-search.k-input {
  min-height: auto;
  height: var(--height-sm);
  margin-bottom: 0;
}
</style>
