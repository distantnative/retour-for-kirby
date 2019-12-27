<template>
  <k-headline
    v-if="hasLogs"
    :data-current="current"
    class="rt-table-switch"
  >
    <k-button
      v-for="(table, index) in tabs"
      :key="table"
      :icon="icons[index]"

      @click="onSwitch(table)"
    >
      {{ $t('retour.' + table) }}
    </k-button>
  </k-headline>
  <p v-else><br/><br/></p>
</template>

<script>
export default {
  computed: {
    current() {
      return this.$store.state.retour.view.table;
    },
    hasLogs() {
      return this.$store.state.retour.options.logs;
    },
    tabs() {
      return ["redirects", "fails"];
    },
    icons() {
      return ["retour", "bug"]
    }
  },
  methods: {
    onSwitch(table) {
      this.$store.dispatch("retour/table", table);
    }
  }
}
</script>

<style lang="scss">
.rt-table-switch {

  > .k-button {
    padding: 0 1rem;
    top: -.2rem;
    font-size: var(--font-size-small);

    &:first-of-type {
      padding-left: 0;
    }
  }

  &[data-current=redirects] > .k-button:nth-of-type(1),
  &[data-current=fails] > .k-button:nth-of-type(2) {
    color: var(--color-focus);
  }

}
</style>

