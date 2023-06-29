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
      <k-pagination
        :details="true"
        :limit="limit"
        :page="page"
        :total="rows.length"
        @paginate="onPaginate"
      />
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
      limit: 50,
    };
  },
  computed: {
    paginatedRows() {
      return this.rows.slice(
        this.limit * (this.page - 1),
        this.limit * this.page
      );
    },
  },
  methods: {
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
  margin-bottom: var(--spacing-3);
}
.retour .list > footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: var(--spacing-2);
}
</style>
