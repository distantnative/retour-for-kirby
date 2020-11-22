<template>
  <div
    v-if="row.hits > 0"
    :title="title"
    class="k-table-count-cell"
  >
    <k-button
      :tooltip="title"
      icon="clock"
    />
    {{ row.hits }}
  </div>
</template>

<script>
export default {
  props: {
    row: Object
  },
  computed: {
    title() {
      return this.$t('retour.hits.last') + ': ' + this.row.last;
    }
  }
}
</script>

<style lang="scss">
.k-table-count-cell {
  display: flex;
  justify-content: space-between;
  padding-left: .5rem;
  padding-right: .5rem;

  .k-icon {
    color: var(--color-border);
    transform: scale(.8);
    cursor: default;
  }
}
</style>
