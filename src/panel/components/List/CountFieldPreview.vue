<template>
  <div :title="title" class="k-count-preview-field">
    <template v-if="row.hits > 0">
      {{ row.hits }}
      <k-icon :title="title" type="clock" />
    </template>
    <!-- eslint-disable vue/singleline-html-element-content-newline -->
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
  padding-left: 0.5rem;
  padding-right: 0.5rem;
}
.k-count-preview-field p {
  color: var(--color-border);
}
.k-count-preview-field .k-icon {
  color: var(--color-border);
  transform: scale(0.8);
  cursor: default;
}
</style>
