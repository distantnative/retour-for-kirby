<template>
  <div class="list">
    <!-- header -->
    <header>
      <div class="filter">
        <label for="filter">
          <k-icon type="search" />
        </label>
        <input
          v-model="filter"
          :data-empty="!filter"
          :placeholder="$t('retour.table.filter')"
          id="filter"
          @keydown.esc.stop="filter = null; $event.target.blur()"
        />
      </div>

      <slot name="button" />
    </header>

    <!-- table -->
    <k-table
      v-if="rows.length"
      :columns="columns"
      :index="limit * (page - 1) + 1"
      :options="options"
      :rows="paginatedRows"
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
          :value="storedLimit"
          @input="onLimit($event.target.value)"
        >
          <option :value="10">10</option>
          <option :value="25">25</option>
          <option :value="50">50</option>
          <option value="all">{{ $t("retour.table.perPage.all") }}</option>
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
import Table from "../../polyfills/Table.vue";

export default {
  components: {
    "k-table": Table
  },
  props: {
    type: String,
    columns: Object,
    options: Array,
    rows: {
      type: Array,
      default() {
        return [];
      }
    },
    empty: String,
  },
  data() {
    return {
      page: 1,
      filter: null
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
      if (!this.limit || this.limit === "all") {
        return this.filteredRows;
      }

      return this.filteredRows.slice(
        this.limit * (this.page - 1),
        this.limit * this.page
      );
    },
    limit() {
      return this.storedLimit === 'all' ? this.rows.length : parseInt(this.storedLimit);
    },
    storedLimit() {
      return sessionStorage.getItem("retour$" + this.type + "$limit") || 10;
    }
  },
  methods: {
    onLimit(limit) {
      this.page = 1;
      sessionStorage.setItem("retour$" + this.type + "$limit", limit);
    },
    onOption(option, row, rowIndex) {
      this.$emit("option", option, row, rowIndex);
    },
    onPaginate(pagination) {
      this.page = pagination.page;
    }
  }
}
</script>

<style>
.retour .list > header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: .75rem;
}
.retour .filter  {
  height: 1.1rem;
  display: flex;
  align-items: center;
}
.retour .filter label {
  cursor: pointer;
  margin-right: .75rem;
}
.retour .filter input {
  background: transparent;
  width: 0;
  outline: none;
  border: 0;
  border-bottom: 1px dotted transparent;
  font-size: var(--font-size-small);
  transition: width .25s, border .25s;
  padding: .15rem;
}
.retour .filter input::placeholder { 
  opacity: 0;
  transition: opacity 1s;
}
.retour .filter input:focus,
.retour .filter input:not([data-empty]) {
  width: 10rem;
  border-bottom-color: #777;
}
.retour .filter input:focus::placeholder,
.retour .filter input:not([data-empty])::placeholder {
  opacity: 1;
  transition: opacity .25s;
}
.retour .list > footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.retour .list > footer > .limit {
  display: flex;
  align-items: center;
  font-size: 0.875rem;
  color: #777;
  height: 2.5rem;
}
</style>
