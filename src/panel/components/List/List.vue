<template>
  <section class="list">
    <!-- header -->
    <header>
      <div />
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
    <k-empty v-else layout="cards">
      {{ empty }}
    </k-empty>

    <!-- footer -->
    <footer>
      <div class="limit">
        <select :value="storedLimit" @input="onLimit($event.target.value)">
          <option :value="10">10</option>
          <option :value="25">25</option>
          <option :value="50">50</option>
          <option value="all">
            {{ $t("retour.table.perPage.all") }}
          </option></select
        >&nbsp;{{ $t("retour.table.perPage.after") }}
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
  </section>
</template>

<script>
export default {
  props: {
    name: String,
    columns: Object,
    options: Array,
    rows: {
      type: Array,
      default() {
        return [];
      },
    },
    empty: String,
  },
  data() {
    return {
      page: 1,
      storedLimit:
        sessionStorage.getItem("retour$" + this.name + "$limit") || 10,
    };
  },
  computed: {
    paginatedRows() {
      if (!this.limit || this.limit === "all") {
        return this.rows;
      }

      return this.rows.slice(
        this.limit * (this.page - 1),
        this.limit * this.page
      );
    },
    limit() {
      return this.storedLimit === "all"
        ? this.rows.length
        : parseInt(this.storedLimit);
    },
  },
  methods: {
    onLimit(limit) {
      this.page = 1;
      this.storedLimit = limit;
      sessionStorage.setItem("retour$" + this.name + "$limit", limit);
    },
    onOption(option, row, rowIndex) {
      this.$emit("option", option, row, rowIndex);
    },
    onPaginate(pagination) {
      this.page = pagination.page;
    },
  },
};
</script>

<style>
.retour .list > header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.75rem;
}
.retour .list > footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.retour .list > footer > .limit {
  display: flex;
  align-items: center;
  font-size: var(--text-sm);
  color: var(--color-gray-600);
  height: 2.5rem;
}
</style>
