<template>
  <div :title="title" class="k-count-preview-field">
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
.k-count-preview-field {
  display: flex;
  flex-direction: row-reverse;
  justify-content: space-between;
  padding-inline: var(--spacing-2);
}
.k-count-preview-field p {
  color: var(--color-gray-400);
}
.k-count-preview-field .k-icon {
  --icon-color: var(--color-gray-400);
  transform: scale(0.8);
}
</style>
