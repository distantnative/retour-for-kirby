<template>
  <div :title="title" class="k-table-count-cell">
    <template v-if="row.hits > 0">
      {{ row.hits }}
      <k-icon :title="title" type="clock" />
    </template>
    <p v-else>–</p>
  </div>
</template>

<script>
export default {
  props: {
    row: Object,
  },
  computed: {
    title() {
      return this.$t("retour.hits.last") + ": " + this.row.last;
    },
  },
};
</script>

<style>
.k-table-count-cell {
  display: flex;
  flex-direction: row-reverse;
  justify-content: space-between;
  padding-left: 0.5rem;
  padding-right: 0.5rem;
}
.k-table-count-cell p {
  color: var(--color-border);
}
.k-table-count-cell .k-icon {
  color: var(--color-border);
  transform: scale(0.8);
  cursor: default;
}
</style>
