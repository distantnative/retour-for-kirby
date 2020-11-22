<template>
  <div class="rt-tbl">

    <!-- header -->
    <header>
      <rt-tbl-filter
        v-model="filter"
        :label="label"
      />
      <slot name="button" />
    </header>

    <!-- table -->
    <k-table
      v-if="rows.length"
      :columns="columns"
      :index="limit * (page - 1) + 1"
      :options="options"
      :rows="filteredRows"
      @cell="$emit('cell', $event)"
      @header="$emit('header', $event)"
      @input="$emit('input', $event)"
      @option="onOption"
    />

    <!-- empty -->
    <k-empty
      v-else
      layout="cards"
    >
      {{ empty }}
    </k-empty>

    <!-- footer -->
    <footer>
      <div class="limit">
        <select
          :value="limit"
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

export default {
  components: {
    "rt-tbl-filter": TableFilter
  },
  props: {
    columns: Object,
    empty: String,
    label: String,
    options: Array,
    rows: {
      type: Array,
      default() {
        return [];
      }
    },
    type: String
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
          if (row[column] && row[column].includes(this.filter) === true) {
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
      localStorage.setItem("retour$" + this.type + "$limit", this.limit);
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

<style lang="scss">
.rt-tbl > header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: .75rem;
}

.rt-tbl > footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.rt-tbl > footer > .limit {
  display: flex;
  align-items: center;
  font-size: 0.875rem;
  color: #777;
  height: 2.5rem;
}
</style>
