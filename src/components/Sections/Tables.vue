<template>
  <k-view>
    <keep-alive>
      <component :is="$store.state.retour.view.table" />
    </keep-alive>
  </k-view>
</template>

<script>
import Fails from "../Tables/Fails.vue";
import Redirects from "../Tables/Redirects.vue";

export default {
  components: {
    fails: Fails,
    redirects: Redirects,
  }
}
</script>

<style lang="scss">
.k-retour-view .tbl {
  .k-icon-url {
    margin-right: .25rem;
    transform: scale(.8);
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
      top: -.25rem;
    }
  }
}
</style>

