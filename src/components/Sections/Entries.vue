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
    Fails,
    Redirects,
  },
  computed: {
    table() {
      return this.$store.state.retour.view.table;
    }
  }
}
</script>

<style lang="scss">
.k-retour-view .tbl {
  .k-icon-url {
    transform: scale(.8);
    margin-right: .25rem;
    color: #d6d6d6;

    &:hover {
      color: #777;
    }
  }

  @media screen and (max-width: 40em) {
    .k-section-header {
      display: block;
    }

    .k-button-group {
      position: relative;
      top: -.25rem;
    }
  }
}
</style>

