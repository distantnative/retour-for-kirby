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
      :rows="normalizedRows"
      @cell="$emit('cell', $event)"
      @header="$emit('header', $event)"
      @option="onOption"
    >
      <template #cell="{ column, value }">
        <p class="k-table-cell-value">
          <template v-if="column.type === 'link'">
            <retour-table-link-preview :value="value" />
          </template>

          <template v-else-if="column.type === 'status'">
            <retour-table-status-preview :value="value" />
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
      class="bg-white p-4 text-center rounded-sm shadow text-gray text-sm"
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
          <option :value="null">{{ $t("retour.tbl.all") }}</option>
        </select>&nbsp;
        {{ $t("retour.tbl.perPage") }}
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
import TableLinkPreview from "./TableLinkPreview.vue";
import TableStatusPreview from "./TableStatusPreview.vue";

export default {
  components: {
    "retour-table-filter": TableFilter,
    "retour-table-link-preview": TableLinkPreview,
    "retour-table-status-preview": TableStatusPreview
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
    const page  = sessionStorage.getItem("retour$" + this.tab + "$page");
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
    normalizedRows() {
      // TODO: remove when fixed in core
      return this.filteredRows.map(row => {
        Object.keys(row).forEach(key => {
          row[key] = row[key] || "";
        });
        if (row.last) {
          row.last = row.last.replace(/-/g, "/");
        }
        return row;
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
      localStorage.setItem("retour$" + this.tab + "$limit", this.limit);
      this.onPaginate({ page: 1 });
    },
    onOption(option, row, rowIndex) {
      this.$emit('option', option, row, rowIndex);
    },
    onPaginate(pagination) {
      this.page = pagination.page;
      sessionStorage.setItem("retour$" + this.tab + "$page", this.page);
    }
  }
}
</script>
