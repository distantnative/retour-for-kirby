<template>
  <k-view>
    <redirects v-show="table === 'redirects'" ref="redirects" />
    <fails v-show="table === 'fails'" ref="fails" />
  </k-view>
</template>

<script>
import Fails from "../Tables/Fails.vue";
import Redirects from "../Tables/Redirects.vue";

export default {
  components: {
    fails: Fails,
    redirects: Redirects
  },
  computed: {
    table() {
      return this.$store.state.retour.view.table;
    }
  }
};
</script>

<style lang="scss">
.k-retour-view .tbl {
  .k-icon-url {
    margin-right: 0.25rem;
    transform: scale(0.8);
    color: var(--color-border);

    &:hover {
      color: var(--color-text-light);
    }
  }

  @media screen and (max-width: 40em) {
    .k-section-header {
      display: block;
    }

    .k-button-group {
      position: relative;
      top: -0.25rem;
    }
  }
}
</style>
