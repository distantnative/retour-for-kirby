<template>
  <k-headline class="rt-table-switch">
    <button
      v-for="table in tabs"
      :key="table"
      :data-current="current === table"
      @click="onSwitch(table)"
    >
      {{ $t('rt.' + table) }}
    </button>
  </k-headline>
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
      if (this.hasLogs === false) {
        return ["redirects"];
      }

      return ["redirects", "fails"];
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
.rt-table-switch > button {
  font-weight: 600;
  text-transform: uppercase;
  padding: .15rem .35rem;
  outline: none;

  &:not(:first-child) {
    margin-left: .5rem;
  }

  &:not(:last-child) {
    margin-right: .5rem;
  }

  &[data-current] {
    border-bottom: 3px solid #4271ae;
  }
}
</style>

