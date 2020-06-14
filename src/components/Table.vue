<template>
  <div class="retour-table">

    <!-- header -->
    <header class="flex items-center justify-between mb-3">
      <retour-table-filter
        v-model="filter"
        :label="$helper.string.ucfirst(tab)"
      />
      <slot name="button" />
    </header>

    <!-- table -->
    <k-table
      :columns="columns"
      :index="limit * (page - 1) + 1"
      :options="options"
      :rows="filteredRows"
      @cell="$emit('cell', $event)"
      @header="$emit('header', $event)"
      @option="onOption"
    >
      <template #cell="{ column, row, value }">
        <p class="k-table-cell-value">
          <template v-if="column.type === 'link'">
            <retour-table-link-cell :value="value" :column="column" />
          </template>

          <template v-else-if="column.type === 'status'">
            <retour-table-status-cell :value="value" :column="column" />
          </template>

          <template v-else-if="column.type === 'priority'">
            <retour-table-priority-cell :value="value" :column="column" />
          </template>

          <template v-else-if="column.type === 'count'">
            <retour-table-count-cell :row="row" />
          </template>

          <template v-else>
            {{ value }}
          </template>
        </p>
      </template>
    </k-table>

    <!-- empty -->
    <div
      v-if="rows.length === 0"
      class="bg-white text-gray p-3 text-center rounded-sm shadow text-sm"
    >
      {{ empty }}
    </div>

    <!-- footer -->
    <footer class="flex items-center justify-between">
      <div class="flex items-center text-sm text-gray" style="height: 2.5rem;">
        <select
          :value="limit"
          class="rounded-sm"
          @input="onLimit($event.target.value)"
        >
          <option :value="10">10</option>
          <option :value="25">25</option>
          <option :value="50">50</option>
          <option :value="null">{{ $t("retour.table.perPage.all") }}</option>
        </select>&nbsp;
        {{ $t("retour.table.perPage.after") }}
      </div>
      <k-pagination
        :details="true"
        :limit="limit"
        :page="page"
        :total="rows.length"
        @paginate="onPaginate"
      />
      <div />
    </footer>

    <!-- dialog -->
    <slot name="dialogs" />
  </div>
</template>

<script>
import TableFilter from "./TableFilter.vue";

import TableCountCell from "./TableCountCell.vue";
import TableLinkCell from "./TableLinkCell.vue";
import TablePriorityCell from "./TablePriorityCell.vue";
import TableStatusCell from "./TableStatusCell.vue";

export default {
  components: {
    "retour-table-filter": TableFilter,
    "retour-table-count-cell": TableCountCell,
    "retour-table-link-cell": TableLinkCell,
    "retour-table-priority-cell": TablePriorityCell,
    "retour-table-status-cell": TableStatusCell
  },
  props: {
    columns: Object,
    empty: String,
    options: Array,
    rows: {
      type: Array,
      default() {
        return [];
      }
    },
    tab: String
  },
  data() {
    const page  = 1;
    const limit = localStorage.getItem("retour$" + this.tab + "$limit");
    return {
      page: parseInt(page) || 1,
      limit: parseInt(limit) || 10,
      filter: null,
      hasFilter: false
    };
  },
  computed: {
    filteredRows() {
      if (!this.filter) {
        return this.rows;
      }

      // get columns that should be filtered
      const columns = Object.keys(this.columns).filter(key => this.columns[key].filter === true);

      // filter rows by checking each column to filter if
      // includes current query
      return this.rows.filter(row => {
        let match = false;

        columns.forEach(column => {
          if (row[column].includes(this.filter) === true) {
            match = true;
          }
        });

        return match === true;
      });
    },
    paginatedRows() {
      if (!this.limit) {
        return this.filteredRows;
      }

      return this.filteredRows.slice(
        this.limit * (this.page - 1),
        this.limit * this.page
      );
    }
  },
  methods: {
    onLimit(limit) {
      this.limit = limit;
      this.page  = 1;
      localStorage.setItem("retour$" + this.tab + "$limit", this.limit);
    },
    onOption(option, row, rowIndex) {
      this.$emit('option', option, row, rowIndex);
    },
    onPaginate(pagination) {
      this.page = pagination.page;
    }
  }
}
</script>
